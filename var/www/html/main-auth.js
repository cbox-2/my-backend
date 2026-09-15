/**
 * NexusBox Main Page Authentication System
 * =========================================
 * Handles Login/Register functionality on the main control panel page
 * Redirects users to chat interface after successful authentication
 * 
 * Features:
 * - Login/Register toggle
 * - Form validation
 * - AJAX API communication
 * - Session management
 * - Security headers handling
 * - Accessibility support
 * 
 * @version 1.0.0
 * @author NexusBox Team
 * @license MIT
 */

(function() {
    'use strict';
    
    // =========================================================================
    // Configuration & Constants
    // =========================================================================
    var CONFIG = {
        API_BASE: '/api/',
        CHAT_REDIRECT: '/public/chat/',
        SESSION_CHECK_INTERVAL: 30000, // 30 seconds
        NOTICE_TIMEOUT: 5000, // 5 seconds
        MIN_PASSWORD_LENGTH: 6,
        USERNAME_PATTERN: /^[a-zA-Z0-9_\-\.]{3,50}$/,
        DEBUG: false
    };
    
    // =========================================================================
    // DOM Element References
    // =========================================================================
    var elements = {
        authForm: document.getElementById('logmain'),
        authLegend: document.getElementById('authLegend'),
        authSubmit: document.getElementById('authSubmit'),
        authToggle: document.getElementById('authToggle'),
        authUsername: document.getElementById('authUsername'),
        authPassword: document.getElementById('authPassword'),
        authConfirm: document.getElementById('authConfirm'),
        confirmLabel: document.getElementById('confirmLabel'),
        authNotice: document.getElementById('authNotice'),
        forgotLink: document.getElementById('forgotLink'),
        qloginForm: document.getElementById('qlogin'),
        popovrBox: document.getElementById('popovr_box'),
        popovrTitle: document.getElementById('popovr_title'),
        popovrText: document.getElementById('popovr_text')
    };
    
    // =========================================================================
    // State Management
    // =========================================================================
    var state = {
        isRegisterMode: false,
        isLoading: false,
        currentUser: null,
        sessionCheckTimer: null
    };
    
    // =========================================================================
    // Utility Functions
    // =========================================================================
    
    /**
     * Log message to console if debug mode is enabled
     * @param {string} message - Message to log
     * @param {string} level - Log level (log, warn, error)
     */
    function debugLog(message, level) {
        if (!CONFIG.DEBUG) return;
        level = level || 'log';
        if (console && console[level]) {
            console[level]('[main-auth] ' + message);
        }
    }
    
    /**
     * Show notification message to user
     * @param {string} message - Message text
     * @param {string} type - Message type (Okay, Error)
     */
    function showNotice(message, type) {
        if (!elements.authNotice) return;
        
        elements.authNotice.textContent = message;
        elements.authNotice.className = 'notice_ok ' + (type || 'Okay');
        elements.authNotice.style.display = 'block';
        elements.authNotice.setAttribute('aria-live', 'polite');
        
        // Auto-hide after timeout
        if (window.noticeTimeout) {
            clearTimeout(window.noticeTimeout);
        }
        window.noticeTimeout = setTimeout(function() {
            if (elements.authNotice) {
                elements.authNotice.style.display = 'none';
            }
        }, CONFIG.NOTICE_TIMEOUT);
        
        debugLog('Notice shown: ' + message + ' (' + type + ')');
    }
    
    /**
     * Show modal popup dialog
     * @param {string} title - Popup title
     * @param {string} content - Popup HTML content
     */
    function showPopup(title, content) {
        if (!elements.popovrBox) return;
        
        if (elements.popovrTitle) {
            elements.popovrTitle.textContent = title;
        }
        if (elements.popovrText) {
            elements.popovrText.innerHTML = content;
        }
        
        elements.popovrBox.style.display = 'block';
        elements.popovrBox.setAttribute('aria-hidden', 'false');
        
        // Focus management for accessibility
        var closeButton = elements.popovrBox.querySelector('button');
        if (closeButton) {
            closeButton.focus();
        }
        
        debugLog('Popup shown: ' + title);
    }
    
    /**
     * Close modal popup dialog
     */
    function closePopup() {
        if (!elements.popovrBox) return;
        
        elements.popovrBox.style.display = 'none';
        elements.popovrBox.setAttribute('aria-hidden', 'true');
        
        // Return focus to trigger element if possible
        if (elements.authToggle && document.activeElement === elements.popovrBox) {
            elements.authToggle.focus();
        }
        
        debugLog('Popup closed');
    }
    
    /**
     * Toggle between Login and Register modes
     */
    function toggleAuthMode() {
        state.isRegisterMode = !state.isRegisterMode;
        
        if (state.isRegisterMode) {
            // Switch to Register mode
            if (elements.authLegend) elements.authLegend.textContent = 'Register';
            if (elements.authSubmit) elements.authSubmit.value = 'Register';
            if (elements.authToggle) elements.authToggle.textContent = 'Have account? Login here';
            if (elements.confirmLabel) elements.confirmLabel.style.display = 'block';
            if (elements.authConfirm) elements.authConfirm.style.display = 'block';
            if (elements.forgotLink) elements.forgotLink.style.display = 'none';
            
            // Clear password fields
            if (elements.authPassword) elements.authPassword.value = '';
            if (elements.authConfirm) elements.authConfirm.value = '';
            
            // Focus username field
            if (elements.authUsername) elements.authUsername.focus();
            
            debugLog('Switched to Register mode');
        } else {
            // Switch to Login mode
            if (elements.authLegend) elements.authLegend.textContent = 'Log in';
            if (elements.authSubmit) elements.authSubmit.value = 'Log in';
            if (elements.authToggle) elements.authToggle.textContent = 'No account? Register here';
            if (elements.confirmLabel) elements.confirmLabel.style.display = 'none';
            if (elements.authConfirm) elements.authConfirm.style.display = 'none';
            if (elements.forgotLink) elements.forgotLink.style.display = 'block';
            
            // Clear password field
            if (elements.authPassword) elements.authPassword.value = '';
            
            // Focus username field
            if (elements.authUsername) elements.authUsername.focus();
            
            debugLog('Switched to Login mode');
        }
    }
    
    /**
     * Open auth form with specific mode
     * @param {string} mode - 'login' or 'register'
     */
    function openAuthMode(mode) {
        if (mode === 'register' && !state.isRegisterMode) {
            toggleAuthMode();
        } else if (mode === 'login' && state.isRegisterMode) {
            toggleAuthMode();
        }
        
        if (elements.authUsername) {
            elements.authUsername.focus();
        }
        
        debugLog('Opened auth mode: ' + mode);
    }
    
    /**
     * Validate username format
     * @param {string} username - Username to validate
     * @returns {boolean} True if valid
     */
    function validateUsername(username) {
        if (!username || username.trim().length < 3) return false;
        return CONFIG.USERNAME_PATTERN.test(username.trim());
    }
    
    /**
     * Validate password strength
     * @param {string} password - Password to validate
     * @returns {boolean} True if valid
     */
    function validatePassword(password) {
        if (!password || password.length < CONFIG.MIN_PASSWORD_LENGTH) return false;
        return true;
    }
    
    /**
     * Show password recovery help
     */
    function showPasswordRecovery() {
        showPopup(
            'Password Recovery',
            '<p>To recover your password, please contact the system administrator with your username or email address.</p>' +
            '<p style="margin-top:1em;"><strong>Admin Contact:</strong><br>' +
            'Email: admin@nexusbox.local<br>' +
            'Support Portal: /support</p>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    /**
     * Show cookie help
     */
    function showCookieHelp() {
        showPopup(
            'Enable Cookies',
            '<p>Cookies must be enabled in your browser to use NexusBox. Here\'s how:</p>' +
            '<ul>' +
            '<li><strong>Chrome:</strong> Settings → Privacy → Cookies → Allow all</li>' +
            '<li><strong>Firefox:</strong> Options → Privacy → Cookies → Accept</li>' +
            '<li><strong>Safari:</strong> Preferences → Privacy → Uncheck "Block all cookies"</li>' +
            '<li><strong>Edge:</strong> Settings → Cookies → Allow sites to save cookies</li>' +
            '</ul>' +
            '<p style="margin-top:1em;">After enabling cookies, refresh this page and try again.</p>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    /**
     * Show help information
     */
    function showHelp() {
        showPopup(
            'Help & Support',
            '<p><strong>NexusBox Help Center</strong></p>' +
            '<ul>' +
            '<li><a href="#getting-started" onclick="closePopup(); return false;">Getting Started</a></li>' +
            '<li><a href="#user-guide" onclick="closePopup(); return false;">User Guide</a></li>' +
            '<li><a href="#troubleshooting" onclick="closePopup(); return false;">Troubleshooting</a></li>' +
            '<li><a href="#contact" onclick="closePopup(); return false;">Contact Support</a></li>' +
            '</ul>' +
            '<p style="margin-top:1em;"><strong>Quick Tips:</strong></p>' +
            '<ul>' +
            '<li>Use a strong password (6+ characters)</li>' +
            '<li>Keep your username private</li>' +
            '<li>Enable cookies for best experience</li>' +
            '</ul>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    /**
     * Show terms of service
     */
    function showTerms() {
        showPopup(
            'Terms of Service',
            '<p><strong>NexusBox Terms of Service</strong></p>' +
            '<p>By using NexusBox, you agree to the following terms:</p>' +
            '<ol>' +
            '<li>Use the service responsibly and respectfully</li>' +
            '<li>Do not share personal or sensitive information</li>' +
            '<li>Follow community guidelines at all times</li>' +
            '<li>Report any abuse or violations to administrators</li>' +
            '</ol>' +
            '<p style="margin-top:1em;"><em>Last updated: January 2024</em></p>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    /**
     * Show privacy policy
     */
    function showPrivacy() {
        showPopup(
            'Privacy Policy',
            '<p><strong>NexusBox Privacy Policy</strong></p>' +
            '<p>We respect your privacy and protect your data:</p>' +
            '<ul>' +
            '<li>Usernames and passwords are encrypted</li>' +
            '<li>Chat messages are stored securely</li>' +
            '<li>We do not share personal data with third parties</li>' +
            '<li>You can request data deletion at any time</li>' +
            '</ul>' +
            '<p style="margin-top:1em;"><em>For questions: privacy@nexusbox.local</em></p>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    /**
     * Show contact information
     */
    function showContact() {
        showPopup(
            'Contact Us',
            '<p><strong>Get in Touch</strong></p>' +
            '<ul>' +
            '<li><strong>Support Email:</strong> support@nexusbox.local</li>' +
            '<li><strong>Technical:</strong> tech@nexusbox.local</li>' +
            '<li><strong>General:</strong> info@nexusbox.local</li>' +
            '</ul>' +
            '<p style="margin-top:1em;"><strong>Response Time:</strong><br>' +
            'We aim to respond within 24 hours during business days.</p>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    /**
     * Show legal notices
     */
    function showNotices() {
        showPopup(
            'Legal Notices',
            '<p><strong>Copyright &amp; Licensing</strong></p>' +
            '<p>&copy; 2024-2026 NexusBox Communications. All rights reserved.</p>' +
            '<p>NexusBox is a trademark of NexusBox Communications (Pty) Ltd.</p>' +
            '<p style="margin-top:1em;"><strong>Third-Party Libraries:</strong></p>' +
            '<ul>' +
            '<li>Open Sans Font - Apache License 2.0</li>' +
            '<li>Numans Font - SIL Open Font License</li>' +
            '</ul>' +
            '<div style="text-align:right;margin-top:1em;">' +
            '<button type="button" onclick="closePopup()">Close</button>' +
            '</div>'
        );
    }
    
    // =========================================================================
    // API Communication Functions
    // =========================================================================
    
    /**
     * Make API request with error handling
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Request data
     * @returns {Promise} Promise with response
     */
    function apiRequest(endpoint, data) {
        state.isLoading = true;
        
        return fetch(CONFIG.API_BASE + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            return response.json();
        })
        .then(function(result) {
            state.isLoading = false;
            return result;
        })
        .catch(function(error) {
            state.isLoading = false;
            debugLog('API Error: ' + error.message, 'error');
            throw error;
        });
    }
    
    /**
     * Handle main authentication form submission
     * @param {Event} event - Form submit event
     * @returns {boolean} False to prevent default submission
     */
    function handleMainAuth(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Get form values
        var username = elements.authUsername ? elements.authUsername.value.trim() : '';
        var password = elements.authPassword ? elements.authPassword.value : '';
        var confirm = elements.authConfirm ? elements.authConfirm.value : '';
        
        // Validate inputs
        if (!username || !password) {
            showNotice('Please fill in all required fields', 'Error');
            if (elements.authUsername && !username) elements.authUsername.focus();
            else if (elements.authPassword && !password) elements.authPassword.focus();
            return false;
        }
        
        if (!validateUsername(username)) {
            showNotice('Username must be 3-50 characters (letters, numbers, _, -, . only)', 'Error');
            elements.authUsername.focus();
            return false;
        }
        
        if (!validatePassword(password)) {
            showNotice('Password must be at least ' + CONFIG.MIN_PASSWORD_LENGTH + ' characters', 'Error');
            elements.authPassword.focus();
            return false;
        }
        
        if (state.isRegisterMode) {
            if (password !== confirm) {
                showNotice('Passwords do not match', 'Error');
                elements.authConfirm.focus();
                return false;
            }
        }
        
        // Show loading state
        if (elements.authSubmit) {
            elements.authSubmit.disabled = true;
            elements.authSubmit.value = state.isRegisterMode ? 'Registering...' : 'Logging in...';
        }
        
        // Determine endpoint
        var endpoint = state.isRegisterMode ? 'register.php' : 'login.php';
        
        // Prepare request data
        var requestData = {
            username: username,
            password: password,
            email: username + '@nexusbox.local'
        };
        
        debugLog('Sending ' + endpoint + ' request for user: ' + username);
        
        // Make API request
        apiRequest(endpoint, requestData)
            .then(function(data) {
                if (data.success) {
                    showNotice(data.message, 'Okay');
                    state.currentUser = data.user;
                    
                    // Redirect to chat after short delay
                    setTimeout(function() {
                        debugLog('Redirecting to chat: ' + CONFIG.CHAT_REDIRECT);
                        window.location.href = CONFIG.CHAT_REDIRECT;
                    }, 1500);
                } else {
                    showNotice(data.message || 'Authentication failed', 'Error');
                    
                    // Re-enable submit button
                    if (elements.authSubmit) {
                        elements.authSubmit.disabled = false;
                        elements.authSubmit.value = state.isRegisterMode ? 'Register' : 'Log in';
                    }
                }
            })
            .catch(function(error) {
                showNotice('Connection error. Please check your internet and try again.', 'Error');
                debugLog('Auth request failed: ' + error.message, 'error');
                
                // Re-enable submit button
                if (elements.authSubmit) {
                    elements.authSubmit.disabled = false;
                    elements.authSubmit.value = state.isRegisterMode ? 'Register' : 'Log in';
                }
            });
        
        return false;
    }
    
    /**
     * Handle quick login form submission (header)
     * @param {Event} event - Form submit event
     * @returns {boolean} False to prevent default submission
     */
    function quickLoginHandler(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        if (!elements.qloginForm) return false;
        
        // Get form values
        var username = elements.qloginForm.querySelector('[name="uname"]');
        var password = elements.qloginForm.querySelector('[name="pword"]');
        
        var usernameValue = username ? username.value.trim() : '';
        var passwordValue = password ? password.value : '';
        
        // Validate
        if (!usernameValue || !passwordValue) {
            alert('Please enter both username and password');
            return false;
        }
        
        // Show loading feedback
        var submitBtn = elements.qloginForm.querySelector('input[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.value = '...';
        }
        
        debugLog('Quick login attempt for: ' + usernameValue);
        
        // Make API request
        apiRequest('login.php', {
            username: usernameValue,
            password: passwordValue,
            email: usernameValue + '@nexusbox.local'
        })
        .then(function(data) {
            if (data.success) {
                debugLog('Quick login successful, redirecting');
                window.location.href = CONFIG.CHAT_REDIRECT;
            } else {
                alert(data.message || 'Login failed');
                
                // Re-enable button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.value = 'Log in';
                }
            }
        })
        .catch(function(error) {
            alert('Connection error. Please try again.');
            debugLog('Quick login error: ' + error.message, 'error');
            
            // Re-enable button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.value = 'Log in';
            }
        });
        
        return false;
    }
    
    /**
     * Check user session status
     */
    function checkSession() {
        debugLog('Checking session status');
        
        fetch(CONFIG.API_BASE + 'session.php', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(function(data) {
            debugLog('Session check result: ' + JSON.stringify(data));
            
            if (data.logged_in && data.user) {
                // User is already logged in
                state.currentUser = data.user;
                
                // Show welcome message
                showNotice('Welcome back, ' + data.user.username + '! Redirecting to chat...', 'Okay');
                
                // Redirect to chat
                setTimeout(function() {
                    window.location.href = CONFIG.CHAT_REDIRECT;
                }, 2000);
            } else {
                // No active session - show login form
                debugLog('No active session, showing login form');
            }
        })
        .catch(function(error) {
            debugLog('Session check failed: ' + error.message, 'warn');
            // Offline mode - allow login form to be used
        });
    }
    
    /**
     * Start periodic session checking
     */
    function startSessionMonitor() {
        if (state.sessionCheckTimer) {
            clearInterval(state.sessionCheckTimer);
        }
        
        state.sessionCheckTimer = setInterval(function() {
            checkSession();
        }, CONFIG.SESSION_CHECK_INTERVAL);
        
        debugLog('Session monitor started (' + CONFIG.SESSION_CHECK_INTERVAL + 'ms interval)');
    }
    
    /**
     * Stop periodic session checking
     */
    function stopSessionMonitor() {
        if (state.sessionCheckTimer) {
            clearInterval(state.sessionCheckTimer);
            state.sessionCheckTimer = null;
            debugLog('Session monitor stopped');
        }
    }
    
    // =========================================================================
    // Event Listeners & Initialization
    // =========================================================================
    
    /**
     * Initialize the authentication system
     */
    function init() {
        debugLog('Initializing NexusBox Auth System');
        
        // Set up form submission handlers
        if (elements.authForm) {
            elements.authForm.addEventListener('submit', handleMainAuth);
        }
        
        if (elements.qloginForm) {
            elements.qloginForm.addEventListener('submit', quickLoginHandler);
        }
        
        // Set up toggle button
        if (elements.authToggle) {
            elements.authToggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleAuthMode();
            });
            
            // Keyboard support for accessibility
            elements.authToggle.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleAuthMode();
                }
            });
        }
        
        // Set up popup close button
        if (elements.popovrBox) {
            // Close on background click
            elements.popovrBox.addEventListener('click', function(e) {
                if (e.target === elements.popovrBox) {
                    closePopup();
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && elements.popovrBox.style.display === 'block') {
                    closePopup();
                }
            });
        }
        
        // Set up global functions for inline event handlers
        window.toggleAuthMode = toggleAuthMode;
        window.openAuthMode = openAuthMode;
        window.handleMainAuth = handleMainAuth;
        window.quickLoginHandler = quickLoginHandler;
        window.showPasswordRecovery = showPasswordRecovery;
        window.showCookieHelp = showCookieHelp;
        window.showHelp = showHelp;
        window.showTerms = showTerms;
        window.showPrivacy = showPrivacy;
        window.showContact = showContact;
        window.showNotices = showNotices;
        window.closePopup = closePopup;
        
        // Initial session check
        checkSession();
        
        // Start session monitoring
        startSessionMonitor();
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopSessionMonitor();
            } else {
                startSessionMonitor();
                checkSession(); // Check immediately when page becomes visible
            }
        });
        
        // Handle beforeunload to clean up
        window.addEventListener('beforeunload', function() {
            stopSessionMonitor();
        });
        
        debugLog('Initialization complete');
    }
    
    // =========================================================================
    // Start the system when DOM is ready
    // =========================================================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already ready
        init();
    }
    
    // Expose for debugging if needed
    if (CONFIG.DEBUG) {
        window.NexusBoxAuth = {
            state: state,
            config: CONFIG,
            toggleAuthMode: toggleAuthMode,
            checkSession: checkSession
        };
    }
    
})();