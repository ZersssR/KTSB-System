/**
 * Tab-Specific Session Manager
 * 
 * This script ensures that the tab_id query parameter persists across page navigations
 * to maintain separate sessions in different tabs.
 */

(function () {
    // Helper to get query param
    function getQueryParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // Helper to add/update query param
    function updateUrlWithTabId(url, tabId) {
        if (!url || url.startsWith('javascript:') || url.startsWith('#') || url.startsWith('blob:')) return url;

        try {
            const urlObj = new URL(url, window.location.origin);
            urlObj.searchParams.set('tab_id', tabId);
            return urlObj.toString();
        } catch (e) {
            // Fallback for relative URLs if URL constructor fails
            const separator = url.includes('?') ? '&' : '?';
            return url + separator + 'tab_id=' + encodeURIComponent(tabId);
        }
    }

    // 1. Check for tab_id in URL
    const urlTabId = getQueryParam('tab_id');

    // 2. Check for tab_id in sessionStorage
    const storedTabId = sessionStorage.getItem('ktsb_tab_id');

    // Logic to determine active tab ID
    let activeTabId = null;

    if (urlTabId) {
        // URL has priority. Update storage if different.
        if (urlTabId !== storedTabId) {
            sessionStorage.setItem('ktsb_tab_id', urlTabId);
            document.cookie = 'ktsb_tab_id=' + encodeURIComponent(urlTabId) + '; path=/; max-age=86400';
        }
        activeTabId = urlTabId;
    } else if (storedTabId) {
        // No URL param, but we have one in storage. 
        // We must reload to attach the session, UNLESS we are on the login page (where we might want to start fresh)
        // or if we are logging out.
        const isLoginPage = window.location.pathname.endsWith('login.php');
        const isLogoutPage = window.location.pathname.endsWith('logout.php');

        if (!isLoginPage && !isLogoutPage) {
            const newUrl = updateUrlWithTabId(window.location.href, storedTabId);
            window.location.replace(newUrl);
            return; // Stop execution while reload happens
        }
        activeTabId = storedTabId;
    }

    // 3. Intercept navigation if we have an active tab ID
    if (activeTabId) {
        document.addEventListener('DOMContentLoaded', function () {
            // A. Intercept Links
            document.body.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link && link.href) {
                    const href = link.getAttribute('href');
                    // Skip anchors, javascript:, and external links
                    if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('http') && !href.startsWith('blob:')) {
                        // It's a relative link, update it
                        link.href = updateUrlWithTabId(link.href, activeTabId);
                    } else if (href && href.startsWith(window.location.origin)) {
                        // It's an absolute internal link
                        link.href = updateUrlWithTabId(link.href, activeTabId);
                    }
                }
            });

            // B. Intercept Forms
            document.body.addEventListener('submit', function (e) {
                const form = e.target;
                if (form.method.toUpperCase() === 'GET') {
                    // For GET forms, add a hidden input
                    let input = form.querySelector('input[name="tab_id"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'tab_id';
                        form.appendChild(input);
                    }
                    input.value = activeTabId;
                } else {
                    // For POST forms, action URL needs the param if the handler expects it in $_GET
                    // But usually PHP looks in $_POST. Let's add it to action URL just in case config.php looks at $_GET
                    const action = form.getAttribute('action') || window.location.href;
                    form.action = updateUrlWithTabId(action, activeTabId);

                    // Also add hidden input for $_POST access
                    let input = form.querySelector('input[name="tab_id"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'tab_id';
                        form.appendChild(input);
                    }
                    input.value = activeTabId;
                }
            });
        });
    }
})();
