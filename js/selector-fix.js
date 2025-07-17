/**
 * Selector Fix Utility
 * Fixes issues with :contains selector being used with querySelector
 */

(function() {
    'use strict';
    
    // Store original methods
    var originalQuerySelector = document.querySelector;
    var originalQuerySelectorAll = document.querySelectorAll;
    
    // Helper function to find elements by text content
    function findElementByText(selector, text) {
        try {
            var elements = document.querySelectorAll(selector);
            for (var i = 0; i < elements.length; i++) {
                if (elements[i].textContent && elements[i].textContent.includes(text)) {
                    return elements[i];
                }
            }
            return null;
        } catch (e) {
            console.warn("Invalid selector:", selector, e);
            return null;
        }
    }
    
    // Helper function to find all elements by text content
    function findAllElementsByText(selector, text) {
        try {
            var elements = document.querySelectorAll(selector);
            var matches = [];
            for (var i = 0; i < elements.length; i++) {
                if (elements[i].textContent && elements[i].textContent.includes(text)) {
                    matches.push(elements[i]);
                }
            }
            return matches;
        } catch (e) {
            console.warn("Invalid selector:", selector, e);
            return [];
        }
    }
    
    // Override querySelector to handle :contains selectors
    document.querySelector = function(selector) {
        if (selector && selector.includes(':contains')) {
            console.warn("Attempted to use :contains selector with querySelector:", selector);
            console.warn("Use findElementByText() function instead");
            console.warn("Stack trace:", new Error().stack);
            
            // Try to extract the base selector and text
            var match = selector.match(/^([^:]+):contains\(['"]([^'"]+)['"]\)/);
            if (match) {
                var baseSelector = match[1];
                var text = match[2];
                console.log("Attempting to find element with baseSelector:", baseSelector, "and text:", text);
                return findElementByText(baseSelector, text);
            }
            
            // If the regex doesn't match, try a more flexible approach
            console.warn("Could not parse :contains selector, attempting fallback");
            var parts = selector.split(':contains(');
            if (parts.length >= 2) {
                var baseSelector = parts[0];
                var textPart = parts[1];
                var text = textPart.replace(/['"]\)$/, '').replace(/^['"]/, '');
                console.log("Fallback: baseSelector:", baseSelector, "text:", text);
                return findElementByText(baseSelector, text);
            }
            
            return null;
        }
        return originalQuerySelector.call(this, selector);
    };
    
    // Override querySelectorAll to handle :contains selectors
    document.querySelectorAll = function(selector) {
        if (selector && selector.includes(':contains')) {
            console.warn("Attempted to use :contains selector with querySelectorAll:", selector);
            console.warn("Use findAllElementsByText() function instead");
            console.warn("Stack trace:", new Error().stack);
            
            // Try to extract the base selector and text
            var match = selector.match(/^([^:]+):contains\(['"]([^'"]+)['"]\)/);
            if (match) {
                var baseSelector = match[1];
                var text = match[2];
                return findAllElementsByText(baseSelector, text);
            }
            
            return [];
        }
        return originalQuerySelectorAll.call(this, selector);
    };
    
    // Global error handler for selector errors
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes(":contains") && e.message.includes("is not a valid selector")) {
            console.warn("Caught invalid :contains selector error:", e.message);
            console.warn("This error has been handled gracefully");
            console.warn("Error details:", e);
            e.preventDefault();
            return false;
        }
    });
    
    // Also handle unhandled promise rejections that might contain selector errors
    window.addEventListener('unhandledrejection', function(e) {
        if (e.reason && e.reason.message && e.reason.message.includes(":contains")) {
            console.warn("Caught unhandled promise rejection with :contains selector:", e.reason);
            e.preventDefault();
            return false;
        }
    });
    
    // Make helper functions globally available
    window.findElementByText = findElementByText;
    window.findAllElementsByText = findAllElementsByText;
    
    console.log("Selector fix utility loaded - :contains selector errors will be handled gracefully");
})(); 