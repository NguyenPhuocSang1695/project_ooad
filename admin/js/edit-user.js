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

  async function loadProvinces(selectEl){
    if (!selectEl) return;
    const data = await fetchJSON('../php/get_provinces.php');
    selectEl.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
    (data||[]).forEach(p=>{
      const o = document.createElement('option');
      o.value = p.province_id; o.textContent = p.name; selectEl.appendChild(o);
    });
  }
  async function loadDistricts(provinceId, selectEl){
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">Chọn quận/huyện</option>';
    if (!provinceId) return;
    const data = await fetchJSON('../php/get_districts.php?province_id=' + encodeURIComponent(provinceId));
    (data||[]).forEach(d=>{ const o=document.createElement('option'); o.value=d.district_id; o.textContent=d.name; selectEl.appendChild(o); });
  }
  async function loadWards(districtId, selectEl){
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">Chọn phường/xã</option>';
    if (!districtId) return;
    const data = await fetchJSON('../php/get_wards.php?district_id=' + encodeURIComponent(districtId));
    (data||[]).forEach(w=>{ const o=document.createElement('option'); o.value=w.ward_id; o.textContent=w.name; selectEl.appendChild(o); });
  }

  // Expose function used by inline onclick
  window.showEditUserPopup = async function(username, userId){
    try{
      const modalEl = document.getElementById('editUserModal');
      if (!modalEl){ alert('Không tìm thấy form sửa người dùng'); return; }
      modalCtrl.open();
      const provinceSel = document.getElementById('edit_province');
      const districtSel = document.getElementById('edit_district');
      const wardSel = document.getElementById('edit_ward');
  const passRow = document.getElementById('edit_password_row');
  const passInput = document.getElementById('edit_password');
  const passConfirm = document.getElementById('edit_confirm_password');
      const usernameInput = document.getElementById('edit_username');
      const originalUsernameInput = document.getElementById('edit_original_username');
  const usernameCol = document.getElementById('edit_username_col');

      await loadProvinces(provinceSel);

      let query = '';
      if (username && username.trim() !== '') {
        query = '?username=' + encodeURIComponent(username);
      } else if (userId) {
        query = '?user_id=' + encodeURIComponent(userId);
      }
      const resp = await fetchJSON('../php/get_user.php' + query);
      if (!resp.success){ alert(resp.message || 'Không tải được thông tin người dùng'); modalCtrl.close(); return; }
      const u = resp.data || {};

      // Fill basics
      // Set hidden keys
      if (document.getElementById('edit_user_id')) {
        document.getElementById('edit_user_id').value = (u.user_id || '');
      }
      originalUsernameInput && (originalUsernameInput.value = u.username || '');
      usernameInput && (usernameInput.value = u.username || '');
  var el;
  if ((el = document.getElementById('edit_fullname'))) el.value = u.fullname || '';
  if ((el = document.getElementById('edit_phone'))) el.value = u.phone || '';
  if ((el = document.getElementById('edit_role'))) el.value = u.role || 'customer';
  if ((el = document.getElementById('edit_status'))) el.value = u.status || 'Active';
  if ((el = document.getElementById('edit_address'))) el.value = u.address_detail || '';

      // Prefill address hierarchy
      if (u.province_id){
        provinceSel.value = String(u.province_id);
        await loadDistricts(u.province_id, districtSel);
      } else { districtSel.innerHTML = '<option value="">Chọn quận/huyện</option>'; }

      if (u.district_id){
        districtSel.value = String(u.district_id);
        await loadWards(u.district_id, wardSel);
      } else { wardSel.innerHTML = '<option value=\"\">Chọn phường/xã</option>'; }

      if (u.ward_id){ wardSel.value = String(u.ward_id); }

      // Determine session and permissions
      let canEditSelf = false;
      let isTargetAdmin = (String(u.role||'').toLowerCase() === 'admin');
      try {
        const sess = await fetchJSON('../php/sessionHandler.php');
        if (sess && sess.status === 'success'){
          const currentUser = String(sess.username || '');
          const currentRole = String(sess.role || '');
          // Admin can edit own username/password only
          canEditSelf = (currentRole === 'admin' && currentUser === (u.username||''));
        }
      } catch(e){ /* ignore session fetch errors */ }

      // Show/hide username + password sections based on target role
      if (!isTargetAdmin){
        // Hide username and password sections entirely for non-admin users
        if (usernameCol) usernameCol.style.display = 'none';
        if (passRow) {
          passRow.style.display = 'none';
          if (passInput) passInput.value = '';
          if (passConfirm) passConfirm.value = '';
        }
      } else {
        // Target is admin: show sections
        if (usernameCol) usernameCol.style.display = '';
        if (passRow) passRow.style.display = '';

        // Only self-admin can actually change
        if (usernameInput){
          if (canEditSelf){
            usernameInput.removeAttribute('readonly');
          } else {
            usernameInput.setAttribute('readonly', 'readonly');
          }
        }
        if (passInput && passConfirm){
          const disabled = !canEditSelf;
          passInput.disabled = disabled;
          passConfirm.disabled = disabled;
          if (disabled){ passInput.value = ''; passConfirm.value = ''; }
        }
      }

      // Cascades
      provinceSel && (provinceSel.onchange = async (e)=>{
        await loadDistricts(e.target.value, districtSel);
        wardSel.innerHTML = '<option value="">Chọn phường/xã</option>';
      });
      districtSel && (districtSel.onchange = async (e)=>{
        await loadWards(e.target.value, wardSel);
      });
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
