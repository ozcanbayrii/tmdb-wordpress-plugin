
// tmdb-ozcanwork/assets/js/admin.js

jQuery(document).ready(function($) {
    
    // 1. Bulk Fetch Logic
    $('#tmdb_fetch_btn').on('click', function(e) {
        e.preventDefault();
        var rawIds = $('#tmdb_ids').val();
        var type = $('input[name="fetch_type"]:checked').val();
        
        if (!rawIds) { alert('ID giriniz.'); return; }

        var ids = rawIds.split(/[\n,]+/).map(function(i){return i.trim();}).filter(function(i){return i!=="";});
        if (ids.length === 0) return;

        var $btn = $(this);
        var $result = $('#tmdb_fetch_result');
        var $progressBar = $('#fetch_progress');
        var $statusText = $('#fetch_status_text');
        
        $btn.prop('disabled', true);
        $('.fetch-progress-wrapper').show();
        $progressBar.val(0);
        $result.html('');

        var total = ids.length;
        var current = 0;

        function processNext() {
            if (current >= total) {
                $btn.prop('disabled', false);
                $statusText.text('Tamamlandı.');
                return;
            }
            var movieId = ids[current];
            $statusText.text('İşleniyor: ' + movieId);

            $.ajax({
                url: tmdb_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'tmdb_fetch_and_create',
                    nonce: tmdb_vars.nonce,
                    movie_id: movieId,
                    type: type
                },
                success: function(res) {
                    if (res.success) $result.append('<div class="fetch-success">✅ ' + res.data.message + '</div>');
                    else $result.append('<div class="fetch-error">❌ ' + res.data + '</div>');
                },
                complete: function() {
                    current++;
                    $progressBar.val((current/total)*100);
                    processNext();
                }
            });
        }
        processNext();
    });

    // 2. Post Editor Meta Box Logic
    
    // Toggle Movie/TV Fields
    $('input[name="tmdb_type_select"]').on('change', function() {
        var type = $(this).val();
        if (type === 'tv') {
            $('.movie-only-field').hide();
            $('.tv-only-field').show();
        } else {
            $('.movie-only-field').show();
            $('.tv-only-field').hide();
        }
    });

    // Instant Fetch
    $('#tmdb_editor_fetch_btn').on('click', function(e) {
        e.preventDefault();
        var movieId = $('#tmdb_id_field').val();
        var type = $('input[name="tmdb_type_select"]:checked').val();
        
        if (!movieId) {
            alert('Lütfen ID girin.');
            return;
        }

        var $btn = $(this);
        var $loading = $('#tmdb_loading_spinner');
        var $msg = $('.tmdb-status-msg');
        var $jsonInput = $('#tmdb_full_data_json');

        $btn.addClass('disabled').prop('disabled', true);
        $loading.show();
        $msg.text('');

        $.ajax({
            url: tmdb_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'tmdb_get_movie_data',
                nonce: tmdb_vars.nonce,
                movie_id: movieId,
                type: type
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Populate Hidden JSON
                    $jsonInput.val(JSON.stringify(data));

                    // Populate Inputs Instantly
                    $('#tmdb_title_input').val(data.title);
                    $('#tmdb_rating_input').val(data.vote_average);
                    $('#tmdb_date_input').val(data.release_date || data.first_air_date);

                    if (type === 'movie') {
                        $('#tmdb_runtime_input').val(data.runtime);
                    } else {
                        $('#tmdb_seasons_input').val(data.number_of_seasons);
                        $('#tmdb_episodes_input').val(data.number_of_episodes);
                    }

                    // --- Auto-fill Post Title ---
                    if (window.wp && wp.data && wp.data.select('core/editor')) {
                         // Gutenberg
                         wp.data.dispatch('core/editor').editPost({ title: data.title });
                    } else if($('#title').val() === '') {
                        // Classic
                        $('#title').val(data.title).trigger('change');
                        $('#title-prompt-text').addClass('screen-reader-text'); // Remove placeholder
                    }

                    // --- Auto-fill Content (Overview) ---
                    var overviewContent = data.overview;
                    if (overviewContent) {
                        if (window.wp && wp.data && wp.data.dispatch && wp.blocks) {
                            // Gutenberg: Create a paragraph block
                            var block = wp.blocks.createBlock('core/paragraph', { content: overviewContent });
                            var currentBlocks = wp.data.select('core/editor').getBlocks();
                            if(currentBlocks.length === 0 || (currentBlocks.length === 1 && currentBlocks[0].attributes.content === '')) {
                                wp.data.dispatch('core/editor').resetBlocks([block]);
                            }
                        } else if (window.tinymce && tinymce.get('content')) {
                            // Classic TinyMCE
                            tinymce.get('content').setContent(overviewContent);
                        } else if ($('#content').length) {
                            // Raw Textarea
                            $('#content').val(overviewContent);
                        }
                    }

                    $msg.html('<span style="color:green">✅ Veriler başarıyla çekildi ve dolduruldu. Kaydetmeyi unutmayın.</span>');
                } else {
                    $msg.html('<span style="color:red">❌ Hata: ' + response.data + '</span>');
                }
            },
            error: function() {
                $msg.html('<span style="color:red">❌ Sunucu hatası.</span>');
            },
            complete: function() {
                $btn.removeClass('disabled').prop('disabled', false);
                $loading.hide();
            }
        });
    });

});
