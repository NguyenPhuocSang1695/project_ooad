(function(){
  async function fetchJSON(url, opts){
    const res = await fetch(url, opts);
    const text = await res.text();
    try { return JSON.parse(text); }
    catch(e){ console.error('Non-JSON response from', url, text); throw new Error('Phản hồi không hợp lệ'); }
  }

  async function handleToggleStatus(userId, currentStatus, onSuccess){
    if (!userId) { alert('Thiếu user_id'); return; }
    const action = currentStatus === 'Active' ? 'khóa' : 'mở khóa';
    const confirm1 = confirm(`Bạn có chắc chắn muốn ${action} người dùng này?`);
    if (!confirm1) return;
    try {
      const fd = new FormData();
      fd.append('user_id', String(userId));
      const resp = await fetchJSON('../php/delete_user.php', { method: 'POST', body: fd });
      if (resp && resp.success){
        alert(resp.message || `Đã ${action} người dùng`);
        if (typeof onSuccess === 'function') onSuccess();
      } else {
        alert(resp.message || `${action.charAt(0).toUpperCase() + action.slice(1)} người dùng thất bại`);
      }
    } catch(e){
      alert(e.message || 'Lỗi mạng');
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    // Detail page single toggle button
    const btn = document.getElementById('toggleUserStatusBtn');
    if (btn){
      btn.addEventListener('click', function(){
        const userId = parseInt(btn.getAttribute('data-user-id') || '0', 10);
        const currentStatus = btn.getAttribute('data-user-status') || 'Active';
        handleToggleStatus(userId, currentStatus, function(){ window.location.reload(); });
      });
    }

    // List page multiple toggle buttons
    document.querySelectorAll('.btn-toggle-status').forEach(function(b){
      b.addEventListener('click', function(ev){
        ev.stopPropagation(); // prevent row navigation
        const tr = b.closest('tr');
        const userId = tr ? parseInt(tr.getAttribute('data-user-id') || '0', 10) : 0;
        const currentStatus = tr ? tr.getAttribute('data-user-status') : 'Active';
        handleToggleStatus(userId, currentStatus, function(){ window.location.reload(); });
      });
    });
  });
})();
