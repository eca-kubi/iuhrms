// Run post view render
// noinspection ES6ConvertVarToLetConst,RegExpRedundantEscape,JSUnusedLocalSymbols,JSDeprecatedSymbols,RegExpSimplifiable,JSUnresolvedReference

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
                }
            });
        }

        // Close any open menu accordions when window is resized below 768px
        window.addEventListener('resize', function() {
            var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);

            if (vw < 768) {
                for (var bsCollapse of sidebarCollapseList) {
                    bsCollapse.hide();
                }
            }
        });
    }

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over

    var fixedNavigation = document.querySelector('body.fixed-nav .sidebar');

    if (fixedNavigation) {
        fixedNavigation.on('mousewheel DOMMouseScroll wheel', function(e) {
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
            template: "<div class='k-widget k-notification k-notification-success' style='color: white;'><div class='k-notification-wrap'><span class='k-icon k-i-info-circle'></span><p>#= message #</p></div></div>"
        }, {
            type: "error",
            template: "<div class='k-widget k-notification k-notification-error' style='color: white;'><div class='k-notification-wrap'><span class='k-icon k-i-info-circle'></span><p>#= message #</p></div></div>"
        }]
    }).data("kendoNotification");
}

// Validate email address to make sure it is an IU email address
function validateEmail(email) {
    return email.match(/^[a-z]+[\.]?[a-z]+[-]?[a-z]*@(iubh\.de|iu\.org)$/i);
}

// Function to create hostelsDataSource

function createHostelsDataSource() {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${BASE_URL}/api/hostels`,
                dataType: "json",
                type: "GET"
            },
            // ... other transport configurations like update, create, delete if needed
        },
        schema: {
            data: 'hostels',
            model: {
                id: 'id',
                fields: {
                    id: { type: 'number' },
                    name: { type: 'string' },
                    description: { type: 'string' },
                    total_rooms: { type: 'number' },
                    occupied_rooms: { type: 'number' },
                    room_types: {
                        type: 'array',
                        model: {
                            id: { type: 'number' },
                            type: { type: 'string' },
                            price: { type: 'number' },
                        }
                    },
                }
            }
        }
    });
}

// Function to create semesterDataSource

function createSemestersDataSource() {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${BASE_URL}/api/semesters`,
                dataType: "json",
                type: "GET"
            },
        },
        schema: {
            data: 'semesters',
            model: {
                id: 'id',
                fields: {
                    id: { type: 'number' },
                    name: { type: 'string' },
                    semester_start: {
                        type: 'object',
                        fields: {
                            date: { type: 'date' },
                            timezone_type: { type: 'number' },
                            timezone: { type: 'string' },
                        }
                    },
                    semester_end: {
                        type: 'object',
                        fields: {
                            date: { type: 'date' },
                            timezone_type: { type: 'number' },
                            timezone: { type: 'string' },
                        }
                    }
                }
            }
        }
    });
}

// Function to create reservationsDataSource
function createReservationsDataSource() {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${BASE_URL}/api/reservations`,
                dataType: 'json',
                type: 'GET',
            },
            create: {
                url: `${BASE_URL}/api/reservations`,
                dataType: 'json',
                type: 'POST',
            },
            update: {
                url: `${BASE_URL}/api/reservations`,
                dataType: 'json',
                type: 'PATCH',
            },
            destroy: {
                url: `${BASE_URL}/api/reservations`,
                dataType: 'json',
                type: 'DELETE',
            },
        },
        schema: {
            data: 'reservations',
            model: {
                id: 'id',
                fields: {
                    id: {type: 'number'},
                    hostel_id: {type: 'number'},
                    user_id: {type: 'number'},
                    room_type_id: {type: 'number'},
                    semester_id: {type: 'number'},
                    status_id: {type: 'number'},
                    reservation_date: {type: 'date'},
                    semester: {
                        type: 'object',
                        fields: {
                            id: {type: 'number'},
                            name: {type: 'string'},
                            semester_start: {
                                type: 'object',
                                fields: {
                                    date: {type: 'date'},
                                    timezone_type: {type: 'number'},
                                    timezone: {type: 'string'},
                                }
                            },
                            semester_end: {
                                type: 'object',
                                fields: {
                                    date: {type: 'date'},
                                    timezone_type: {type: 'number'},
                                    timezone: {type: 'string'},
                                }
                            }
                        }
                    },
                    status: {type: 'object'},
                    hostel: {type: 'object'},
                    room_type: {type: 'object'},
                    user: {type: 'object'},
                },
            }
        },
        // Sort the reservations by request date in descending order
        sort: {
            field: 'reservation_date',
            dir: 'desc'
        },
        error: function(e) {
            console.error("DataSource Error: ", e);
        },
    });
}

// Function to create roomTypesDataSource
function createRoomTypesDataSource() {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${BASE_URL}/api/room-types`,
                dataType: 'json',
                type: 'GET',
            },
        },
        schema: {
            data: 'room_types',
            model: {
                id: 'id',
                fields: {
                    id: {type: 'number'},
                    type: {type: 'string'},
                    price: {type: 'number'},
                },
            }
        },
        error: function(e) {
            console.error("DataSource Error: ", e);
        },
    });
}