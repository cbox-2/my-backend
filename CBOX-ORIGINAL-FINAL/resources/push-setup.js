"use strict";
/* 
	Push notification subscription manager. 
	
	Good fun with the serviceWorker, PushManager and showNotification APIs. 
	Only works on Chrome, but the flip side is that it works on Chrome ANDROID too! 
	
	Process is quite epic and this script is a mess. We register the service worker, 
	produce the UI (button) for subscription, sends subscribe/unsubscribe commands to the server, 
	and try to monitor window visibility/focus state for purposes of masking notifications for 
	the current channel. 
	
	It can also receive messages from the service worker for logging purposes. 
	
	Button "subscribe" meaning varies:
		Subscribe -> request permission -> get token -> link channel
		Subscribe -> (have permission) -> get token -> link channel
		Subscribe -> (have token) -> link channel
	
	Button "unsubscribe" meaning is actually deregister. We don't destroy our copy of the endpoint token, 
	nor instruct the browser to eliminate notifications for this site. Doing so is silly not because we have 
	to ask for a new endpoint token next time, but because of our one-to-many endpoint-channel mapping, where 
	only on unsubscription from the last remaining channel is it even valid behaviour to remove the endpoint. 
	
	TODO: 
		- Subscription refresh and masking functionality.
			Done some masking work but it's stymied by the use-case, where it's actually a giant OR for 
			visibility between two iframes and the current window. So the rest of this awaits integration with 
			Cbox. I think the best approach is to handle the backend calls and the delays and any watchdog timers 
			we need right here, but then call .setMask() from outside. 
			
		- There are some possible error states we don't handle properly, e.g. leaving button disabled. Must check em. 
			In particular, need to define what we do with the main error state (no serviceworker/push support.) Just leave 
			button hidden?
			
		- Tidy up the fetch() stuff and replace the XHR with fetch() and that will help solve the above problem. 
			Hopefully fetch() is available everywhere PushManager is...
			
		
	
*/
(function () {

var log = function () {
	console.log.apply(console, arguments);
	console.trace();
}

var pushMgr = window['pushMgr'] = {
	state: "unavailable",
	endpoint: "",
	unsubscribe: function () {},
	subscribe: function () {},
	onmessage: function () {},
	init: function (chanID, swPath, regPath, cbStateChange) {
	
	// Some early exits on feature tests. 
	if (!'serviceWorker' in navigator) {
		log("No service worker support. ");
		return false;
	}
	
	// Are Notifications supported in the service worker?  
	if (typeof ServiceWorkerRegistration !== 'function' || !('showNotification' in ServiceWorkerRegistration.prototype)) {  
		log("Service workers don't support notifications.");
		return;  
	}
	
	// Check if push messaging is supported  
	if (!('PushManager' in window)) {  
		log("Push messaging not supported.");
		return;
	}
	
	if (!chanID) {
		// No channel: can't do anything.
		return;
	}
	
	log("Started push setup");
	
	var setState = function (newState) {
		log(pushMgr.state+" -> "+newState);
		
		if (pushMgr.state === newState) {
			return;
		}

		cbStateChange(newState);
		pushMgr.state = newState;
	}

	var postSubscription = function (sub, isRegistering, success) {
		var mode = (isRegistering ? 'subscribe' : 'unsubscribe');
		
		log("Recording "+mode+" for channel "+chanID+" endpoint ..."+sub.endpoint.substring(sub.endpoint.length-20));
		
		var http = new XMLHttpRequest();
		var url = regPath;
		var params = "endpoint="+encodeURIComponent(sub.endpoint)+"&cid="+encodeURIComponent(chanID)+"&mode="+encodeURIComponent(mode);
		
		http.open("POST", url, true);

		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		
		http.onreadystatechange = function() {
			if(http.readyState == 4 && (http.status == 200 || http.status == 204)) {
				log("Request successful.");
				if (typeof success === "function") {
					success();
				}
			}
		}
		http.send(params);
	}
	
	var doSubscribe = pushMgr.subscribe = function () {
		setState("busy");
		
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  
			return serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly:true});
		})
		.then(function(subscription) {  
			// The subscription was successful  
			pushMgr.endpoint = subscription.endpoint;
			
			postSubscription(subscription, true, function () {
				// TODO: Promise?
				setState("subscribed");				
			});

		})
		.catch(function(e) {  
			log("Unable to subscribe to push.", e);
			
			setState("ready");
		});
	}
	
	/*
		This doesn't actually unsubscribe in the sense of telling the browser to delete the push subscription.
		It just unregisters the user for the channel. 
	*/
	var doUnsubscribe = pushMgr.unsubscribe = function () {
		setState("busy");
		
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  
			return serviceWorkerRegistration.pushManager.getSubscription();
		})
		.then(function(subscription) {  
		
			// Check we have a subscription to unsubscribe
			// TODO: Seems unnecessary? 
			
			if (!subscription) {  
				setState("ready");
				return;  
			}  

			log("Unsubscribing for "+subscription.endpoint.substring(subscription.endpoint.length-20));
			
			postSubscription(subscription, false, function () {
				// TODO: Success? Promise?
			});
			
			return true;
			
			// Not actually unsubscribing the device!
			//return subscription.unsubscribe();
		})
		.then(function(successful) {
				setState("ready");
		})
		.catch(function(e) {  
			log('Error thrown while unsubscribing.', e);
			setState("ready");
		});  
	}
	
	
	/*
		Masking is an optimization intended to suppress push altogether when we are sure the user is "active" on the page. 
		
		It's disabled for now as premature; getting it right is difficult, especially since the oasis of Page Visibility turns 
		out to be something of a mirage, on Windows at least. Also NB is that on Android, event handlers may be pre-empted by, 
		say, the screen turning off, so the last-known state of a foreground page with the screen fully off may well be "focused and visible". 
		
		Service workers waking up on push will be aware the screen is off: suppressing (or in this case, NOT suppressing) notifications 
		reliably therefore depends on them, which is unfortunate since a) there's coupling when the document of interest is not the 
		window client (think iframes) and b) we're on orders from Google to obey the userVisibleOnly flag.
		
		For now, the service worker simply suppresses notification display when it sees a visible and focused client. 
		(If userVisibleOnly means anything sensible, then this is not really a crime: we're still making a message visible to the user.)
		
		The service worker also implements "marking", which is subtly different from masking, and a bit of a hack. 
		It sets the cursor for subsequent notification fetches so that we only get new messages on each push-induced fetch. 
		
		Because we're not storing any state in the SW and only doing unidirectional comms, to populate a "composite" multi-message 
		notification we allow the cursor to lag so that we fetch the same earlier messages repeatedly. 
		
		This has the downside of only "catching up" on the second push after re-focus; I'm not fixing this because "mark" is not a 
		concept that I want in the service worker at all: the SW should be storing state between invocations and deciding based 
		on that state what data to fetch and (possibly in combination with upstream postMessage) whether or not to show a notification. 
		When push finally supports data payloads, then stored state makes even more sense. 
		
	*/
	var windowFocus = true;
	var lastMaskSend = 0;
	var curMaskMode = "";
	var maskDelay = null;
	
	var setMask = function () {
	
		if (pushMgr.state !== "subscribed") {
			// Checking state in here is a hint that this whole routine belongs in the caller. 
			return false;
		}
	
		var d = new Date().getTime() - lastMaskSend;
	
		// TODO: Move this out to the caller.
		var mode = "unmask";
		if (!document.hidden && windowFocus) {
			mode = "mask";
		}
		
		//log("Got setmask with "+mode+" and prev "+curMaskMode);
		
		if (maskDelay !== null) {
			//log("Canceled mask delay");
			window.clearTimeout(maskDelay);
			maskDelay = null;
		}
		
		if (curMaskMode == mode && mode == "mask" && d < 15000) {
			// Mode is mask already and recently refreshed. Delay longer.
			// Not tested...
			maskDelay = window.setTimeout(setMask, 30000-d);		
			return;
		}
		
		if (curMaskMode == mode && mode == "unmask") {
			// Mode is unmask already; no need to send an update
			return;
		}
		
		if (mode == "mask" && maskDelay === null && d < 5000) {
			// Delay
			maskDelay = window.setTimeout(setMask, 5000);
			return;
		}

		if (mode == "unmask") {
			// Send immediately
		}
	
		log("Mode "+mode+" ");
		/*
		fetch(regPath, {
				method: 'post',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: "mode="+mode+"&endpoint="+encodeURIComponent(endpoint)+"&cid="+encodeURIComponent(chanID)+"&ttl=60"
		});
		*/
		
		curMaskMode = mode;
		lastMaskSend = new Date().getTime();
	}
	
	/*
	document.addEventListener("visibilitychange", function (e) {
		log("Page visibility: "+!document.hidden);
		
		mask(!document.hidden);
		
	}, false);
	*/
	window.addEventListener("focus", function () {
		windowFocus = true;
		setMask();
	});
	
	window.addEventListener("blur", function () {
		windowFocus = false;
		setMask();
		//autoScroll = false;
	});
	
	window.addEventListener("visibilitychange", function () {
		setMask();
	});
	
	window.setInterval(function () {
		setMask();
	}, 30000);
	
	
	navigator.serviceWorker.addEventListener('message', function(event) {
		var data = event.data;
		
		console.log(data);
		
		if (typeof(data) !== "object") {
			log("[SW Message] "+data);
			return;
		}
		
		pushMgr.onmessage(data);
	});


	setState("busy");
		
	// Register service worker and then check the current registration state for this sub/channel. 
	navigator.serviceWorker.register(swPath).then(function () { 
		log("Service worker registered");

		// Check the current Notification permission. If its denied, it's a permanent block until the  user changes the permission.
		// TODO: What to do about button here? Should tell this user he needs to allow us.
		if (Notification.permission === 'denied') {  
			log("Notifications are user-denied.");
			setState("disabled");
			return;  
		}

		// We need the service worker registration to check for a subscription  
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  
			log("Service worker ready");

			// Do we already have a push message subscription?  
			serviceWorkerRegistration.pushManager.getSubscription().then(function(sub) {
			
				if (!sub) {  
					log("No existing subscription found.");
					setState("ready");

					return;  
				}
				
				var endpoint = pushMgr.endpoint = sub.endpoint;

				log("Found existing sub. Checking reg state for chan "+chanID+" on sub ..."+sub.endpoint.substring(sub.endpoint.length-20));
				
				return fetch(regPath, {
					method: 'post',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: "mode=check&endpoint="+encodeURIComponent(endpoint)+"&cid="+encodeURIComponent(chanID)
				});

			})
			.then(function (resp) {
				if (!resp || !resp.text) {
					return 0;
				}
				return resp.text();
			})
			.then(function (val) {
				if (val == "true") {
					setState("subscribed");
				}
				else {
					setState("ready");
				}
			})
			.catch(function (e) {
				log("Problem getting subscription", e);
				setState("unavailable");
			});
		})
		.catch(function (e) {
			log("Problem waiting on worker ready", e);
			setState("unavailable");
		});
	})
	.catch(function (e) {
		log("Problem registering service worker", e);
		setState("unavailable");
	});
}
}

if (window['pushMgrReady']) {
	window['pushMgrReady']();
}

}());