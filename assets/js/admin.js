jQuery(function ($) {
    loadPosts();

    $('#xarma-post-type').on('change', function () {
        loadPosts($(this).val());
    });

    $('#xarma-lang-filter').on('change', function () {
        loadPosts($('#xarma-post-type').val());
    });

    function loadPosts(postType = 'post') {
        const lang = $('#xarma-lang-filter').val();

        $.post(xarmaData.ajaxUrl, {
            action: 'xarma_get_posts',
            nonce: xarmaData.nonce,
            post_type: postType,
            lang: lang
        }, function (response) {
            if (response.success && Array.isArray(response.data)) {
                let rows = '';
                response.data.forEach(function (post) {
                    rows += `<tr data-id="${post.ID}">
                        <td class="handle">⇅</td>
                        <td><input type="checkbox" class="bulk-check" value="${post.ID}"></td>
                        <td contenteditable="true" class="xarma-edit" data-field="title">${post.title}</td>
                        <td>
                            <select class="xarma-edit" data-field="status">
                                <option value="publish" ${post.status === 'publish' ? 'selected' : ''}>Pubblicato</option>
                                <option value="draft" ${post.status === 'draft' ? 'selected' : ''}>Bozza</option>
                                <option value="trash" ${post.status === 'trash' ? 'selected' : ''}>Cestino</option>
                            </select>
                        </td>
                        <td><input type="date" class="xarma-edit" data-field="date" value="${post.date}"></td>
                        <td><input type="text" class="xarma-edit" data-field="color" value="${post.meta_color || ''}"></td>
                        <td>${post.lang || ''}</td>
                        <td>
                            <button class="clone-post button">Clona</button>
                            <button class="trash-post button">${post.status === 'trash' ? 'Ripristina' : 'Cestina'}</button>
                        </td>
                    </tr>`;
                });
                $('#xarma-sheet-table tbody').html(rows);
                enableDragDrop();
            }
        });
    }

    $(document).on('change blur', '.xarma-edit', function () {
        const $cell = $(this);
        const $tr = $cell.closest('tr');
        const post_id = $tr.data('id');
        const field = $cell.data('field');
        const value = $cell.val() || $cell.text();

        $.post(xarmaData.ajaxUrl, {
            action: 'xarma_update_post',
            nonce: xarmaData.nonce,
            post_id: post_id,
            field: field,
            value: value
        });
    });

    function enableDragDrop() {
        $('#xarma-sheet-table tbody').sortable({
            handle: '.handle',
            update: function (e, ui) {
                const order = [];
                $('#xarma-sheet-table tbody tr').each(function (i) {
                    order.push({ id: $(this).data('id'), order: i + 1 });
                });
                $.post(xarmaData.ajaxUrl, {
                    action: 'xarma_update_order',
                    nonce: xarmaData.nonce,
                    order: order
                });
            }
        });
    }

    $(document).on('click', '.clone-post', function () {
        const post_id = $(this).closest('tr').data('id');
        $.post(xarmaData.ajaxUrl, {
            action: 'xarma_clone_post',
            nonce: xarmaData.nonce,
            post_id: post_id
        }, function () {
            loadPosts($('#xarma-post-type').val());
        });
    });

    $('#new-post-btn').on('click', function () {
        const title = prompt('Titolo del nuovo post');
        if (!title) return;
        $.post(xarmaData.ajaxUrl, {
            action: 'xarma_new_post',
            nonce: xarmaData.nonce,
            title: title,
            post_type: $('#xarma-post-type').val()
        }, function () {
            loadPosts($('#xarma-post-type').val());
        });
    });

    $(document).on('click', '.trash-post', function () {
        const post_id = $(this).closest('tr').data('id');
        const doAction = $(this).text() === 'Cestina' ? 'trash' : 'restore';
        $.post(xarmaData.ajaxUrl, {
            action: 'xarma_trash_post',
            nonce: xarmaData.nonce,
            post_id: post_id,
            do: doAction
        }, function () {
            loadPosts($('#xarma-post-type').val());
        });
    });

    $('.xarma-col-toggle').on('change', function () {
        const col = $(this).data('col');
        const visible = $(this).is(':checked');
        const index = $(`th[data-col="${col}"]`).index() + 1;
        $(`#xarma-sheet-table td:nth-child(${index}), th:nth-child(${index})`).toggle(visible);
    });

    $('#xarma-toggle-dark').on('change', function () {
        $('body').toggleClass('xarma-dark', $(this).is(':checked'));
    });

    $('#xarma-toggle-compact').on('change', function () {
        $('body').toggleClass('xarma-compact', $(this).is(':checked'));
    });

    let autosaveBuffer = {};
    setInterval(() => {
        const pending = Object.entries(autosaveBuffer);
        if (pending.length === 0) return;
        pending.forEach(([post_id, fields]) => {
            Object.entries(fields).forEach(([field, value]) => {
                $.post(xarmaData.ajaxUrl, {
                    action: 'xarma_update_post',
                    nonce: xarmaData.nonce,
                    post_id: post_id,
                    field: field,
                    value: value
                });
            });
        });
        autosaveBuffer = {};
    }, 30000);

    $(document).on('input change blur', '.xarma-edit', function () {
        const $el = $(this);
        const $tr = $el.closest('tr');
        const post_id = $tr.data('id');
        const field = $el.data('field');
        const value = $el.val() || $el.text();

        if (!autosaveBuffer[post_id]) {
            autosaveBuffer[post_id] = {};
        }
        autosaveBuffer[post_id][field] = value;
    });
});