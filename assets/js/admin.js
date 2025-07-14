jQuery(document).ready(function ($) {
  const $tableBody = $('#xarma-sheet-table tbody');
  const $postType = $('#xarma-post-type');
  const $lang = $('#xarma-lang-filter');
  const $filter = $('#xarma-filter-input');
  const $deleteBtn = $('#delete-selected-btn');

  function loadPosts() {
    $.post(xarmaData.ajaxUrl, {
      action: 'xarma_get_posts',
      nonce: xarmaData.nonce,
      post_type: $postType.val(),
      lang: $lang.val()
    }, function (res) {
      if (!res || !res.success || !Array.isArray(res.data.posts)) {
        alert('❌ Errore nel caricamento contenuti.');
        return;
      }

      renderTable(res.data.posts, res.data.authors || []);
    });
  }

  function renderTable(posts, authors) {
    $tableBody.empty();
    posts.forEach(post => {
      const statusOptions = ['publish', 'draft', 'pending', 'private']
        .map(status => `<option value="${status}" ${post.status === status ? 'selected' : ''}>${status}</option>`)
        .join('');

      const authorOptions = authors.map(user =>
        `<option value="${user.ID}" ${user.ID == post.author ? 'selected' : ''}>${user.display_name}</option>`
      ).join('');

      const row = `
        <tr data-id="${post.ID}">
          <td><input type="checkbox" class="row-check"></td>
          <td><input type="text" class="xarma-edit" data-field="title" value="${escapeHtml(post.title)}"></td>
          <td><select class="xarma-edit" data-field="status">${statusOptions}</select></td>
          <td><input type="date" class="xarma-edit" data-field="date" value="${post.date}"></td>
          <td><input type="color" class="xarma-edit" data-field="color" value="${post.meta_color || '#ffffff'}"></td>
          <td><input type="text" class="xarma-edit" data-field="slug" value="${escapeHtml(post.slug)}"></td>
          <td><input type="text" class="xarma-edit" data-field="excerpt" value="${escapeHtml(post.excerpt || '')}"></td>
          <td><select class="xarma-edit" data-field="author">${authorOptions}</select></td>
          <td>
            <button class="edit-content" data-id="${post.ID}" data-content="${escapeHtml(post.content)}">✏️</button>
            <button class="delete-post" data-id="${post.ID}">🗑️</button>
          </td>
          <td>${post.lang}</td>
        </tr>`;
      $tableBody.append(row);
    });

    bindEvents();
    updateDeleteButtonVisibility();
  }

  function bindEvents() {
    $('.xarma-edit').on('change', function () {
      const $row = $(this).closest('tr');
      const id = $row.data('id');
      const field = $(this).data('field');
      const value = $(this).val();

      $row.removeClass('xarma-saved').addClass('xarma-updating');

      $.post(xarmaData.ajaxUrl, {
        action: 'xarma_save_post',
        nonce: xarmaData.nonce,
        post_id: id,
        field: field,
        value: value
      }, function (res) {
        $row.removeClass('xarma-updating');
        if (res && res.success) {
          $row.addClass('xarma-saved');
          setTimeout(() => $row.removeClass('xarma-saved'), 1200);
        } else {
          alert('❌ Errore salvataggio');
        }
      });
    });

    $('.edit-content').on('click', function () {
      const id = $(this).data('id');
      const content = $(this).data('content') || '';
      $('#xarma-modal-id').val(id);
      $('#xarma-modal-content').val(content);
      $('#xarma-modal').fadeIn(200);
    });

    $tableBody.off('click', '.delete-post');
    $tableBody.on('click', '.delete-post', function () {
      const id = $(this).data('id');
      if (confirm('Eliminare questo contenuto?')) {
        $.post(xarmaData.ajaxUrl, {
          action: 'xarma_delete_post',
          nonce: xarmaData.nonce,
          post_ids: [id]
        }, function () {
          showToast('✅ Post eliminato');
          loadPosts();
        });
      }
    });

    $tableBody.off('change', '.row-check');
    $tableBody.on('change', '.row-check', function () {
      $(this).closest('tr').toggleClass('selected', this.checked);
      updateDeleteButtonVisibility();
    });

    $('#check-all').off('change').on('change', function () {
      const checked = this.checked;
      $('.row-check').each(function () {
        this.checked = checked;
        $(this).closest('tr').toggleClass('selected', checked);
      });
      updateDeleteButtonVisibility();
    });
  }

  function updateDeleteButtonVisibility() {
    const hasSelected = $('.row-check:checked').length > 0;
    $deleteBtn.toggleClass('hidden', !hasSelected);
  }

  $('#delete-selected-btn').on('click', function () {
    const ids = $('.row-check:checked').map(function () {
      return $(this).closest('tr').data('id');
    }).get();

    if (ids.length === 0) {
      alert('Nessun contenuto selezionato.');
      return;
    }

    if (confirm(`Eliminare ${ids.length} elementi selezionati?`)) {
      $.post(xarmaData.ajaxUrl, {
        action: 'xarma_delete_post',
        nonce: xarmaData.nonce,
        post_ids: ids
      }, function () {
        showToast('✅ Contenuti eliminati');
        loadPosts();
      });
    }
  });

  $('#xarma-modal-save').on('click', function () {
    const id = $('#xarma-modal-id').val();
    const content = $('#xarma-modal-content').val();

    $.post(xarmaData.ajaxUrl, {
      action: 'xarma_save_post',
      nonce: xarmaData.nonce,
      post_id: id,
      field: 'content',
      value: content
    }, function (res) {
      $('#xarma-modal').fadeOut(200);
      if (res && res.success) {
        showToast('✅ Contenuto aggiornato');
        loadPosts();
      } else {
        alert('❌ Errore salvataggio contenuto');
      }
    });
  });

  $('#xarma-modal-cancel').on('click', function () {
    $('#xarma-modal').fadeOut(200);
  });

  // ✅ Filtro migliorato: cerca nei campi rilevanti
  $filter.on('input', function () {
    const search = $(this).val().toLowerCase();

    $('#xarma-sheet-table tbody tr').each(function () {
      const title = $(this).find('input[data-field="title"]').val()?.toLowerCase() || '';
      const slug = $(this).find('input[data-field="slug"]').val()?.toLowerCase() || '';
      const excerpt = $(this).find('input[data-field="excerpt"]').val()?.toLowerCase() || '';

      const match = title.includes(search) || slug.includes(search) || excerpt.includes(search);
      $(this).toggle(match);
    });
  });

  $('#new-post-btn').on('click', function () {
    $.post(xarmaData.ajaxUrl, {
      action: 'xarma_new_post',
      nonce: xarmaData.nonce,
      post_type: $postType.val()
    }, function () {
      loadPosts();
    });
  });

  $postType.on('change', loadPosts);
  $lang.on('change', loadPosts);

  function escapeHtml(text) {
    return text ? text.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
  }

  function showToast(msg) {
    const $toast = $('#xarma-toast');
    if ($toast.length === 0) {
      $('body').append('<div id="xarma-toast"></div>');
    }
    $('#xarma-toast').text(msg).fadeIn(200).delay(1000).fadeOut(400);
  }

  loadPosts();
});