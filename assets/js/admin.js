jQuery(document).ready(function ($) {
  const $tableBody = $('#xarma-sheet-table tbody');
  const $postType = $('#xarma-post-type');
  const $lang = $('#xarma-lang-filter');
  const $filter = $('#xarma-filter-input');

  function loadPosts() {
    $.post(xarmaData.ajaxUrl, {
      action: 'xarma_get_posts',
      nonce: xarmaData.nonce,
      post_type: $postType.val(),
      lang: $lang.val()
    }, function (res) {
      if (!res || !res.success || !res.data || !Array.isArray(res.data.posts)) {
        alert('❌ Errore nel caricamento contenuti. Controlla console.');
        console.log(res);
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
          <td><button class="edit-content" data-id="${post.ID}" data-content="${escapeHtml(post.content)}">✏️</button></td>
          <td>${post.lang}</td>
        </tr>`;
      $tableBody.append(row);
    });

    addEvents();
  }

  function addEvents() {
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
          alert('❌ Errore nel salvataggio!');
          console.log(res);
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
  }

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
        alert('❌ Errore nel salvataggio contenuto');
        console.log(res);
      }
    });
  });

  $('#xarma-modal-cancel').on('click', function () {
    $('#xarma-modal').fadeOut(200);
  });

  $filter.on('input', function () {
    const search = $(this).val().toLowerCase();
    $('#xarma-sheet-table tbody tr').each(function () {
      const text = $(this).text().toLowerCase();
      $(this).toggle(text.includes(search));
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