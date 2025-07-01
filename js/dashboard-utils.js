/**
 * Dashboard Utilities
 * Common functions and error handlers for all dashboards
 */

// Global error handler for failed script loads
function handleScriptError(scriptName) {
    console.error(`Failed to load ${scriptName}. Some features may not work correctly.`);
    
    // Show user-friendly error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-warning alert-dismissible fade show';
    errorDiv.style.position = 'fixed';
    errorDiv.style.top = '10px';
    errorDiv.style.right = '10px';
    errorDiv.style.zIndex = '9999';
    errorDiv.style.maxWidth = '400px';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Notice:</strong> ${scriptName} failed to load. Some advanced features may not be available.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Insert at the beginning of body
    if (document.body) {
        document.body.insertBefore(errorDiv, document.body.firstChild);
    } else {
        // If body not ready, wait for DOM
        document.addEventListener('DOMContentLoaded', function() {
            document.body.insertBefore(errorDiv, document.body.firstChild);
        });
    }
    
    // Auto-dismiss after 10 seconds
    setTimeout(function() {
        if (errorDiv && errorDiv.parentNode) {
            errorDiv.remove();
        }
    }, 10000);
}

// Network connectivity checker
function checkNetworkConnectivity() {
    return navigator.onLine;
}

// Fallback loader for external dependencies
function loadScriptWithFallback(primaryUrl, fallbackUrl, scriptName) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = primaryUrl;
        
        script.onload = function() {
            console.log(`Successfully loaded ${scriptName} from primary source`);
            resolve();
        };
        
        script.onerror = function() {
            console.warn(`Failed to load ${scriptName} from primary source, trying fallback...`);
            
            // Remove failed script
            script.remove();
            
            // Try fallback
            const fallbackScript = document.createElement('script');
            fallbackScript.src = fallbackUrl;
            
            fallbackScript.onload = function() {
                console.log(`Successfully loaded ${scriptName} from fallback source`);
                resolve();
            };
            
            fallbackScript.onerror = function() {
                console.error(`Failed to load ${scriptName} from both primary and fallback sources`);
                handleScriptError(scriptName);
                reject(new Error(`Failed to load ${scriptName}`));
            };
            
            document.head.appendChild(fallbackScript);
        };
        
        document.head.appendChild(script);
    });
}

// Dashboard initialization helper
function initializeDashboard(config) {
    const {
        dependencies = [],
        onReady = function() {},
        onError = function(error) { console.error('Dashboard initialization error:', error); }
    } = config;
    
    // Load dependencies
    const loadPromises = dependencies.map(dep => {
        if (dep.fallback) {
            return loadScriptWithFallback(dep.primary, dep.fallback, dep.name);
        } else {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = dep.primary;
                script.onload = resolve;
                script.onerror = () => {
                    handleScriptError(dep.name);
                    reject(new Error(`Failed to load ${dep.name}`));
                };
                document.head.appendChild(script);
            });
        }
    });
    
    // Wait for all dependencies and DOM
    Promise.all([
        ...loadPromises,
        new Promise(resolve => {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', resolve);
            } else {
                resolve();
            }
        })
    ]).then(() => {
        try {
            onReady();
        } catch (error) {
            onError(error);
        }
    }).catch(onError);
}

// Common dashboard functions
const DashboardUtils = {
    handleScriptError,
    checkNetworkConnectivity,
    loadScriptWithFallback,
    initializeDashboard,
    
    // Format numbers for display
    formatNumber: function(num, decimals = 0) {
        if (num === null || num === undefined || isNaN(num)) return 'N/A';
        return Number(num).toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    },
    
    // Format percentages
    formatPercentage: function(num, decimals = 1) {
        if (num === null || num === undefined || isNaN(num)) return 'N/A';
        return Number(num).toFixed(decimals) + '%';
    },
    
    // Show loading indicator
    showLoading: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        }
    },
    
    // Hide loading indicator
    hideLoading: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const spinner = element.querySelector('.fa-spinner');
            if (spinner) {
                spinner.closest('div').remove();
            }
        }
    },
    
    // Show error message
    showError: function(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>${message}</div>`;
        }
    }
};

// Make available globally
window.DashboardUtils = DashboardUtils;
window.handleScriptError = handleScriptError;
