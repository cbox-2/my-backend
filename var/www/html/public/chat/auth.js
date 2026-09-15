/**
 * NexusBox Auth System
 * Pure JavaScript Login/Register Module
 * Works with PHP API: /public/api/
 */
(function() {
    'use strict';
    
    console.log('[auth.js] NexusBox Auth System Loaded');
    
    // === العناصر الأساسية من الواجهة ===
    var btnProfile = document.getElementById('btnProfile');
    var authForm = document.getElementById('authForm');
    var authSubmit = document.getElementById('authSubmit');
    var logRegText = document.getElementById('logRegText');
    var authMsg = document.getElementById('authMsg');
    var nameInput = document.querySelector('input[name="nme"]');
    var chatForm = document.getElementById('frmMain');
    var btnLogout = document.getElementById('btnLogout');
    var pwordInput = authForm ? authForm.querySelector('input[name="pword"]') : null;
    var pword2Input = authForm ? authForm.querySelector('input[name="pword2"]') : null;
    
    // === المتغيرات الداخلية ===
    var currentUser = null;
    var isRegisterMode = false;
    var API_BASE = '/public/api/';
    
    // === 1. فحص الجلسة عند تحميل الصفحة ===
    checkSession();
    
    // === 2. إعداد زر تسجيل الدخول/إنشاء حساب ===
    if (btnProfile && authForm) {
        btnProfile.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('[auth.js] Profile button clicked');
            toggleAuthForm();
        });
        console.log('[auth.js] btnProfile connected');
    } else {
        console.warn('[auth.js] btnProfile or authForm not found');
    }
    
    // === 3. إظهار/إخفاء نافذة التسجيل ===
    function toggleAuthForm() {
        if (!authForm) return;
        
        if (authForm.style.display === 'block') {
            // إخفاء النافذة
            authForm.style.display = 'none';
            console.log('[auth.js] Auth form hidden');
        } else {
            // إظهار النافذة مع تنسيق مركّز
            authForm.style.display = 'block';
            authForm.style.position = 'fixed';
            authForm.style.top = '50%';
            authForm.style.left = '50%';
            authForm.style.transform = 'translate(-50%, -50%)';
            authForm.style.background = '#fff';
            authForm.style.padding = '25px';
            authForm.style.borderRadius = '10px';
            authForm.style.boxShadow = '0 5px 30px rgba(0,0,0,0.4)';
            authForm.style.zIndex = '9999';
            authForm.style.minWidth = '320px';
            authForm.style.maxWidth = '90%';
            
            // إخفاء عناصر غير ضرورية مؤقتاً
            var captcha = document.getElementById('captcha');
            if (captcha) captcha.style.display = 'none';
            var pgProfile = document.getElementById('pgProfile');
            if (pgProfile) pgProfile.style.display = 'none';
            
            // تركيز على حقل كلمة المرور
            if (pwordInput) pwordInput.focus();
            
            console.log('[auth.js] Auth form shown');
        }
    }
    
    // === 4. التبديل بين Login و Register ===
    if (authMsg) {
        authMsg.style.cursor = 'pointer';
        authMsg.style.color = '#059ad0';
        authMsg.style.textDecoration = 'underline';
        
        authMsg.addEventListener('click', function() {
            isRegisterMode = !isRegisterMode;
            
            if (isRegisterMode) {
                // وضع التسجيل
                logRegText.textContent = 'Register';
                authMsg.textContent = 'Have account? Login here';
                if (pword2Input) pword2Input.style.display = 'block';
            } else {
                // وضع الدخول
                logRegText.textContent = 'Log in';
                authMsg.textContent = 'No account? Register here';
                if (pword2Input) pword2Input.style.display = 'none';
            }
            console.log('[auth.js] Mode toggled: ' + (isRegisterMode ? 'register' : 'login'));
        });
    }
    
    // === 5. معالجة إرسال النموذج ===
    if (authSubmit && authForm) {
        authSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            handleSubmit();
        });
        
        // دعم ضغط Enter للإرسال
        if (pwordInput) {
            pwordInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSubmit();
                }
            });
        }
    }
    
    // === 6. دالة الإرسال للـ API ===
    function handleSubmit() {
        var username = nameInput ? nameInput.value.trim() : '';
        var password = pwordInput ? pwordInput.value : '';
        var password2 = pword2Input ? pword2Input.value : '';
        
        // تحقق من المدخلات
        if (!username || !password) {
            alert('Please enter username and password');
            return;
        }
        
        if (isRegisterMode) {
            if (password.length < 6) {
                alert('Password must be at least 6 characters');
                return;
            }
            if (password !== password2) {
                alert('Passwords do not match');
                return;
            }
        }
        
        // تحديد endpoint
        var endpoint = isRegisterMode ? API_BASE + 'register.php' : API_BASE + 'login.php';
        
        console.log('[auth.js] Sending request to ' + endpoint);
        
        // إرسال الطلب
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: username,
                password: password,
                email: username + '@nexusbox.local'
            })
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(function(data) {
            console.log('[auth.js] Response:', data);
            
            if (data.success) {
                // تطبيق حالة تسجيل الدخول
                applyLogin(data.user);
                
                // إخفاء النافذة
                authForm.style.display = 'none';
                
                // رسالة نجاح
                alert('✅ ' + data.message);
                
                console.log('[auth.js] User logged in: ' + data.user.username);
            } else {
                // رسالة خطأ
                alert('❌ ' + (data.message || 'Operation failed'));
            }
        })
        .catch(function(error) {
            console.error('[auth.js] Error:', error);
            alert('Connection error. Please check your internet.');
        });
    }
    
    // === 7. تطبيق حالة تسجيل الدخول على الواجهة ===
    function applyLogin(user) {
        if (!user) return;
        
        currentUser = user;
        
        // قفل حقل الاسم وعرض اسم المستخدم
        if (nameInput) {
            nameInput.value = user.username;
            nameInput.disabled = true;
            nameInput.style.opacity = '0.7';
            nameInput.style.backgroundColor = 'rgba(0,0,0,0.05)';
            nameInput.style.cursor = 'not-allowed';
        }
        
        // إخفاء زر Login/Register
        if (btnProfile) {
            btnProfile.style.display = 'none';
        }
        
        // إظهار زر Logout وربط الحدث
        if (btnLogout) {
            btnLogout.style.display = 'block';
            btnLogout.onclick = doLogout;
        }
        
        // إضافة حقل user_id المخفي للفورم الرئيسي
        var hiddenInput = chatForm ? chatForm.querySelector('input[name="user_id"]') : null;
        if (!hiddenInput && chatForm) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'user_id';
            chatForm.appendChild(hiddenInput);
        }
        if (hiddenInput) {
            hiddenInput.value = user.id;
        }
        
        console.log('[auth.js] UI updated for user: ' + user.username);
    }
    
    // === 8. دالة تسجيل الخروج ===
    function doLogout() {
        console.log('[auth.js] Logout requested');
        
        fetch(API_BASE + 'logout.php', { method: 'POST' })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            console.log('[auth.js] Logout response:', data);
            
            // إعادة تعيين الواجهة
            currentUser = null;
            
            if (nameInput) {
                nameInput.disabled = false;
                nameInput.value = '';
                nameInput.style.opacity = '1';
                nameInput.style.backgroundColor = '';
                nameInput.style.cursor = '';
            }
            
            if (btnProfile) {
                btnProfile.style.display = 'block';
            }
            
            if (btnLogout) {
                btnLogout.style.display = 'none';
                btnLogout.onclick = null;
            }
            
            var hiddenInput = chatForm ? chatForm.querySelector('input[name="user_id"]') : null;
            if (hiddenInput) {
                hiddenInput.value = '';
            }
            
            // إعادة تحميل الصفحة لتحديث الجلسة
            location.reload();
        })
        .catch(function(error) {
            console.error('[auth.js] Logout error:', error);
            // حتى مع الخطأ، نعيد تعيين الواجهة محلياً
            location.reload();
        });
    }
    
    // === 9. فحص الجلسة النشطة عند التحميل ===
    function checkSession() {
        console.log('[auth.js] Checking session...');
        
        fetch(API_BASE + 'session.php')
        .then(function(response) {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(function(data) {
            console.log('[auth.js] Session check:', data);
            
            if (data.logged_in && data.user) {
                // جلسة نشطة: تطبيق حالة الدخول
                applyLogin(data.user);
                console.log('[auth.js] Session restored for: ' + data.user.username);
            } else {
                // لا يوجد جلسة: المستخدم ضيف
                console.log('[auth.js] No active session (guest mode)');
            }
        })
        .catch(function(error) {
            console.log('[auth.js] Session check skipped (offline/error):', error);
            // في وضع Offline، نعتبر المستخدم ضيف
        });
    }
    
    // === 10. تعديل إرسال الرسائل لربطها بالمستخدم ===
    // هذه الدالة تربط user_id مع كل رسالة ترسل
    if (chatForm) {
        var originalOnSubmit = chatForm.onsubmit;
        
        chatForm.onsubmit = function(e) {
            // إذا المستخدم مسجل دخول، نضمن إرسال user_id
            if (currentUser && currentUser.id) {
                var hiddenInput = chatForm.querySelector('input[name="user_id"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'user_id';
                    chatForm.appendChild(hiddenInput);
                }
                hiddenInput.value = currentUser.id;
                console.log('[auth.js] Message will include user_id: ' + currentUser.id);
            }
            
            // استدعاء الدالة الأصلية إذا وجدت
            if (typeof originalOnSubmit === 'function') {
                return originalOnSubmit.call(this, e);
            }
            return true;
        };
    }
    
    // === 11. دعم الضغط على Enter في حقل الاسم لفتح نافذة الدخول ===
    if (nameInput && btnProfile) {
        nameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !nameInput.disabled) {
                e.preventDefault();
                btnProfile.click();
            }
        });
    }
    
    console.log('[auth.js] Initialization complete');
    
})();