// Admin Panel JavaScript

$(document).ready(function() {
    // Sidebar toggle for mobile
    $('#sidebarToggle').on('click', function() {
        $('#adminSidebar').toggleClass('active');
    });

    // Get CSRF token
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || 
               $('input[name="csrf_token"]').val() || '';
    }

    // Drag and Drop for Properties
    if ($('#sortableProperties').length) {
        $('#sortableProperties').sortable({
            handle: '.drag-handle',
            axis: 'y',
            cursor: 'move',
            opacity: 0.8,
            update: function(event, ui) {
                var sortedIds = [];
                $('#sortableProperties tr').each(function() {
                    sortedIds.push($(this).data('id'));
                });

                // Send AJAX request
                $.ajax({
                    url: '/admin/properties/api/reorder.php',
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-Token': getCsrfToken()
                    },
                    data: JSON.stringify({
                        sorted_ids: sortedIds,
                        csrf_token: getCsrfToken()
                    }),
                    success: function(response) {
                        if (response.success) {
                            showNotification('Порядок объектов обновлен', 'success');
                        } else {
                            showNotification('Ошибка при обновлении порядка', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Ошибка при обновлении порядка', 'error');
                        location.reload(); // Reload on error
                    }
                });
            }
        });
    }

    // Toggle Featured
    $('.btn-toggle-featured').on('click', function() {
        var $btn = $(this);
        var propertyId = $btn.data('id');
        var currentFeatured = $btn.data('featured') === '1';

        $.ajax({
            url: '/admin/properties/api/toggle_featured.php',
            method: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-Token': getCsrfToken()
            },
            data: JSON.stringify({
                id: propertyId,
                csrf_token: getCsrfToken()
            }),
            success: function(response) {
                if (response.success) {
                    $btn.data('featured', response.featured ? '1' : '0');
                    if (response.featured) {
                        $btn.html('<i class="fas fa-star text-warning"></i>');
                    } else {
                        $btn.html('<i class="far fa-star"></i>');
                    }
                    showNotification('Статус обновлен', 'success');
                }
            },
            error: function() {
                showNotification('Ошибка при обновлении статуса', 'error');
            }
        });
    });

    // Delete Property
    $('.btn-delete-property').on('click', function() {
        var $btn = $(this);
        var propertyId = $btn.data('id');
        var propertyTitle = $btn.data('title');

        if (!confirm('Вы уверены, что хотите удалить объект "' + propertyTitle + '"?\n\nЭто действие нельзя отменить.')) {
            return;
        }

        $.ajax({
            url: '/admin/properties/api/delete.php',
            method: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-Token': getCsrfToken()
            },
            data: JSON.stringify({
                id: propertyId,
                csrf_token: getCsrfToken()
            }),
            success: function(response) {
                if (response.success) {
                    $btn.closest('tr').fadeOut(300, function() {
                        $(this).remove();
                    });
                    showNotification('Объект удален', 'success');
                } else {
                    showNotification('Ошибка при удалении', 'error');
                }
            },
            error: function() {
                showNotification('Ошибка при удалении', 'error');
            }
        });
    });

    // Photo Preview
    $('#photos').on('change', function(e) {
        var files = e.target.files;
        var preview = $('#photoPreview');
        preview.empty();

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var reader = new FileReader();

            reader.onload = (function(file) {
                return function(e) {
                    var div = $('<div class="photo-item"></div>');
                    var img = $('<img>').attr('src', e.target.result);
                    div.append(img);
                    preview.append(div);
                };
            })(file);

            reader.readAsDataURL(file);
        }
    });

    // Remove Photo
    $(document).on('click', '.btn-remove-photo', function() {
        var photoId = $(this).data('id');
        var $item = $(this).closest('.photo-item');

        if (confirm('Удалить это фото?')) {
            // TODO: Implement photo deletion via API
            $item.fadeOut(300, function() {
                $(this).remove();
            });
        }
    });

    // Map Integration (Google Maps)
    var mapInitialized = false;
    var googleMap = null;
    var googleMarker = null;
    
    // Function to initialize the map
    function initializeGoogleMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            alert('Google Maps API не загружен. Пожалуйста, проверьте API ключ и попробуйте снова.');
            return false;
        }

        var lat = parseFloat($('#lat').val()) || 39.4699;
        var lng = parseFloat($('#lng').val()) || -0.3763;

        // Initialize Google Map
        var mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 12,
            mapTypeId: 'roadmap'
        };

        googleMap = new google.maps.Map(document.getElementById('map'), mapOptions);

        // Create marker (using standard Marker, AdvancedMarkerElement requires additional setup)
        googleMarker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: googleMap,
            draggable: true,
            title: 'Расположение объекта'
        });

        // Update coordinates when marker is dragged
        google.maps.event.addListener(googleMarker, 'dragend', function(event) {
            var position = googleMarker.getPosition();
            $('#lat').val(position.lat());
            $('#lng').val(position.lng());
        });

        // Update marker position when map is clicked
        google.maps.event.addListener(googleMap, 'click', function(event) {
            var clickedLocation = event.latLng;
            googleMarker.setPosition(clickedLocation);
            $('#lat').val(clickedLocation.lat());
            $('#lng').val(clickedLocation.lng());
        });

        mapInitialized = true;
        return true;
    }
    
    if ($('#map').length) {
        $('#selectOnMap').on('click', function() {
            var $map = $('#map');
            $map.slideToggle();

            if (!mapInitialized) {
                // Check if Google Maps API is loaded
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    // Wait for API to load
                    var checkInterval = setInterval(function() {
                        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                            clearInterval(checkInterval);
                            if (initializeGoogleMap()) {
                                // Map initialized successfully
                            }
                        }
                    }, 100);
                    
                    // Timeout after 5 seconds
                    setTimeout(function() {
                        clearInterval(checkInterval);
                        if (!mapInitialized) {
                            alert('Не удалось загрузить Google Maps API. Пожалуйста, проверьте API ключ.');
                        }
                    }, 5000);
                } else {
                    initializeGoogleMap();
                }
            } else {
                // If map is already initialized, update marker position if coordinates changed
                var currentLat = parseFloat($('#lat').val());
                var currentLng = parseFloat($('#lng').val());
                if (!isNaN(currentLat) && !isNaN(currentLng) && googleMap && googleMarker) {
                    var position = new google.maps.LatLng(currentLat, currentLng);
                    googleMarker.setPosition(position);
                    googleMap.setCenter(position);
                }
            }
        });
    }

    // Form Validation
    $('.property-form').on('submit', function(e) {
        var videoUrl = $('#video_url').val();
        
        // Validate URL format only if video URL is provided
        if (videoUrl && videoUrl.trim() !== '') {
            try {
                new URL(videoUrl);
            } catch (err) {
                e.preventDefault();
                showNotification('Некорректный формат URL видео', 'error');
                $('#video_url').focus();
                return false;
            }
        }
    });

    // Simple notification system
    function showNotification(message, type) {
        type = type || 'info';
        var alertClass = 'alert-' + (type === 'error' ? 'error' : 'success');
        var $notification = $('<div class="alert ' + alertClass + ' notification">' + 
                           message + '</div>');
        
        $('.admin-main').prepend($notification);
        
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut(300);
    }, 5000);
});

