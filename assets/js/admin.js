jQuery(document).ready(function ($) {
  const $tableBody = $('#xarma-sheet-table tbody');
  const $postType = $('#xarma-post-type');
  const $lang = $('#xarma-lang-filter');
  const $filter = $('#xarma-filter-input');

  function loadPosts() {
    console.log('🔄 Caricamento per tipo:', $postType.val());

    $.post(xarmaData.ajaxUrl, {
      action: 'xarma_get_posts',
      nonce: xarmaData.nonce,
      post_type: $postType.val(),
      lang: $lang.val()
    }, function (res) {
      if (res.success) {
        renderTable(res.data);
      } else {
        console.warn('❌ Errore risposta AJAX:', res);
      }
    });
  }

  function renderTable(data) {
    $tableBody.empty();
    data.forEach(post => {
      const statusOptions = ['publish', 'draft', 'pending', 'private', 'trash']
        .map(status => `<option value="${status}" ${post.status === status ? 'selected' : ''}>${status.charAt(0).toUpperCase() + status.slice(1)}</option>`)
        .join('');

      const row = `
        <tr data-id="${post.ID}">
          <td class="handle">☰</td>
          <td><input type="checkbox" class="row-check"></td>
          <td><input type="text" class="xarma-edit" data-field="title" value="${escapeHtml(post.title)}" title="Titolo"></td>
          <td data-col="status">
            <select class="xarma-edit" data-field="status" title="Stato">
              ${statusOptions}
            </select>
          </td>
          <td data-col="date"><input type="date" class="xarma-edit" data-field="date" value="${post.date}" title="Data"></td>
          <td data-col="color"><input type="color" class="xarma-edit" data-field="color" value="${post.meta_color || '#ffffff'}" title="Colore"></td>
          <td>${post.lang}</td>
          <td>
            <button class="clone-post">📄</button>
            <button class="trash-post">🗑️</button>
          </td>
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
      $row.addClass('xarma-updating');

      $.post(xarmaData.ajaxUrl, {
        action: 'xarma_save_post',
        nonce: xarmaData.nonce,
        post_id: id,
        field: field,
        value: value
      }, function () {
        $row.removeClass('xarma-updating').addClass('xarma-saved');
        showToast('✅ Salvataggio riuscito');
        setTimeout(() => $row.removeClass('xarma-saved'), 1000);
      });
    });
  }

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

  function showToast(msg) {
    const $toast = $('#xarma-toast');
    if ($toast.length === 0) {
      $('body').append('<div id="xarma-toast"></div>');
    }
    $('#xarma-toast').text(msg).fadeIn(200).delay(1000).fadeOut(400);
  }

  function escapeHtml(text) {
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  loadPosts();
});