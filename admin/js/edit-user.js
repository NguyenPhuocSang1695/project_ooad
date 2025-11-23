(function(){
  const modalCtrl = {
    el: null,
    open(){
      this.el = document.getElementById('editUserModal');
      if (this.el){
        this.el.classList.add('show');
        // remove inline display:none so flex centering works
        this.el.style.removeProperty('display');
        document.body.style.overflow = 'hidden';
      }
    },
    close(){
      if (this.el){
        this.el.classList.remove('show');
        this.el.style.display = 'none';
        document.body.style.overflow = '';
      }
    }
  };

  async function fetchJSON(url, opts){
    const res = await fetch(url, opts);
    const text = await res.text();
    try { return JSON.parse(text); }
    catch(e){ console.error('Non-JSON response from', url, text); throw new Error('Phản hồi không hợp lệ'); }
  }

  // Expose function used by inline onclick
  window.showEditUserPopup = async function(username, userId){
    try{
      const modalEl = document.getElementById('editUserModal');
      if (!modalEl){ alert('Không tìm thấy form sửa người dùng'); return; }
      modalCtrl.open();

      const originalUsernameInput = document.getElementById('edit_original_username');

      let query = '';
      if (username && username.trim() !== '') {
        query = '?username=' + encodeURIComponent(username);
      } else if (userId) {
        query = '?user_id=' + encodeURIComponent(userId);
      }
      const resp = await fetchJSON('../php/get_user.php' + query);
      if (!resp.success){ alert(resp.message || 'Không tải được thông tin người dùng'); modalCtrl.close(); return; }
      const u = resp.data || {};

      // Fill hidden fields and editable fields
      if (document.getElementById('edit_user_id')) {
        document.getElementById('edit_user_id').value = (u.user_id || '');
      }
      if (originalUsernameInput) {
        originalUsernameInput.value = u.username || '';
      }
      
      var el;
      if ((el = document.getElementById('edit_fullname'))) el.value = u.fullname || '';
      if ((el = document.getElementById('edit_phone'))) el.value = u.phone || '';
      if ((el = document.getElementById('edit_role'))) el.value = u.role || 'customer';

    } catch (e){
      console.error(e); alert(e.message || 'Lỗi mở form sửa người dùng'); modalCtrl.close();
    }
  };

  document.addEventListener('DOMContentLoaded', function(){
    const modalEl = document.getElementById('editUserModal');
    if (modalEl){
      const headerClose = modalEl.querySelector('.btn-close');
      const cancelBtn = document.getElementById('cancelEditUser');
      headerClose && headerClose.addEventListener('click', ()=> modalCtrl.close());
      cancelBtn && cancelBtn.addEventListener('click', ()=> modalCtrl.close());

      // Submit
      const form = document.getElementById('editUserForm');
      form && form.addEventListener('submit', async function(e){
        e.preventDefault();
        try{
          const fd = new FormData(form);
          const out = await fetchJSON('../php/update_user.php', { method: 'POST', body: fd });
          if (out.success){ alert(out.message || 'Cập nhật thành công'); modalCtrl.close(); window.location.reload(); }
          else { alert(out.message || 'Cập nhật thất bại'); }
        } catch(err){ alert(err.message || 'Lỗi mạng'); }
      });

      // Close on backdrop click
      modalEl.addEventListener('click', function(ev){ if (ev.target === modalEl) modalCtrl.close(); });
    }
  });
})();

