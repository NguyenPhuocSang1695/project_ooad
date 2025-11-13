(function(){
  async function fetchJSON(url, opts){
    const res = await fetch(url, opts);
    const text = await res.text();
    try { return JSON.parse(text); }
    catch(e){ console.error('Non-JSON response from', url, text); throw new Error('Phản hồi không hợp lệ'); }
  }

  async function handleDelete(userId, onSuccess){
    if (!userId) { alert('Thiếu user_id'); return; }
    const confirm1 = confirm('Bạn có chắc chắn muốn xóa người dùng này?');
    if (!confirm1) return;
    const confirm2 = confirm('Hành động này không thể hoàn tác. Xác nhận xóa?');
    if (!confirm2) return;
    try {
      const fd = new FormData();
      fd.append('user_id', String(userId));
      const resp = await fetchJSON('../php/delete_user.php', { method: 'POST', body: fd });
      if (resp && resp.success){
        alert(resp.message || 'Đã xóa người dùng');
        if (typeof onSuccess === 'function') onSuccess();
      } else {
        alert(resp.message || 'Xóa người dùng thất bại');
      }
    } catch(e){
      alert(e.message || 'Lỗi mạng');
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    // Detail page single delete button
    const btn = document.getElementById('deleteUserBtn');
    if (btn){
      btn.addEventListener('click', function(){
        const userId = parseInt(btn.getAttribute('data-user-id') || '0', 10);
        handleDelete(userId, function(){ window.location.href = 'customer.php'; });
      });
    }

    // List page multiple delete buttons
    document.querySelectorAll('.btn-delete-user').forEach(function(b){
      b.addEventListener('click', function(ev){
        ev.stopPropagation(); // prevent row navigation
        const tr = b.closest('tr');
        const userId = tr ? parseInt(tr.getAttribute('data-user-id') || '0', 10) : 0;
        handleDelete(userId, function(){ window.location.reload(); });
      });
    });
  });
})();
