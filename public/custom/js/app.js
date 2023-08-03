// Run post view render
function postViewRender() {
    "use strict"; // Start of use strict

    var sidebar = document.querySelector('.sidebar');
    var sidebarToggles = document.querySelectorAll('#sidebarToggle, #sidebarToggleTop');

    if (sidebar) {

        var collapseEl = sidebar.querySelector('.collapse');
        var collapseElementList = [].slice.call(document.querySelectorAll('.sidebar .collapse'))
        var sidebarCollapseList = collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, { toggle: false });
        });

        for (var toggle of sidebarToggles) {

            // Toggle the side navigation
            toggle.addEventListener('click', function(e) {
                document.body.classList.toggle('sidebar-toggled');
                sidebar.classList.toggle('toggled');

                if (sidebar.classList.contains('toggled')) {
                    for (var bsCollapse of sidebarCollapseList) {
                        bsCollapse.hide();
                    }
                };
            });
        }

        // Close any open menu accordions when window is resized below 768px
        window.addEventListener('resize', function() {
            var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);

            if (vw < 768) {
                for (var bsCollapse of sidebarCollapseList) {
                    bsCollapse.hide();
                }
            };
        });
    }

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over

    var fixedNaigation = document.querySelector('body.fixed-nav .sidebar');

    if (fixedNaigation) {
        fixedNaigation.on('mousewheel DOMMouseScroll wheel', function(e) {
            var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);

            if (vw > 768) {
                var e0 = e.originalEvent,
                    delta = e0.wheelDelta || -e0.detail;
                this.scrollTop += (delta < 0 ? 1 : -1) * 30;
                e.preventDefault();
            }
        });
    }

    var scrollToTop = document.querySelector('.scroll-to-top');

    if (scrollToTop) {

        // Scroll to top button appear
        window.addEventListener('scroll', function() {
            var scrollDistance = window.pageYOffset;

            //check if user is scrolling up
            if (scrollDistance > 100) {
                scrollToTop.style.display = 'block';
            } else {
                scrollToTop.style.display = 'none';
            }
        });
    }

    // Create avatars with Initials js
    $('.img-profile').initial();
}

/** Override window.getComputedStyle to check that the elem is an Element node and not a text node
 *  This is needed because the original function will throw an error if the elem is a text node
 * */
var _getComputedStyle = window.getComputedStyle;
window.getComputedStyle = function (elem, pseudoElt){
    if (elem.nodeType !== 1) {
        return null
    }
    return _getComputedStyle(elem, pseudoElt);
}

/**
 * Create notification using Kendo UI notification widget
 */
function getNotification() {
    return $("<div/>").kendoNotification({
        // Center the notification horizontally and vertically at the top of the screen
        position: {
            top: 200,
            left: "40%"
        },
        autoHideAfter: 6000,
        stacking: "down",
        templates: [{
            type: "success",
            template: "<div class='k-widget k-notification k-notification-success' style='color: \\#fff;'><div class='k-notification-wrap'><span class='k-icon k-i-info-circle'></span><p>#= message #</p></div></div>"
        }, {
            type: "error",
            template: "<div class='k-widget k-notification k-notification-error' style='color: \\#fff;'><div class='k-notification-wrap'><span class='k-icon k-i-info-circle'></span><p>#= message #</p></div></div>"
        }]
    }).data("kendoNotification");
}

