<!-- 修改密碼 Modal -->
<div id="change-password-modal-global" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>修改密碼</h2>
            <button class="modal-close" onclick="closePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="change-password-form-global">
                <div class="form-group">
                    <label>目前密碼</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>新密碼</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>確認新密碼</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closePasswordModal()">取消</button>
            <button class="btn-primary" onclick="submitChangePasswordGlobal()">確認修改</button>
        </div>
    </div>
</div>

<script>
    function closePasswordModal() {
        document.getElementById('change-password-modal-global').style.display = 'none';
    }

    function openPasswordModal() {
        document.getElementById('change-password-modal-global').style.display = 'flex';
    }

    // 修改密碼按鈕事件
    document.addEventListener('DOMContentLoaded', function() {
        const changePwBtn = document.getElementById('change-password-btn-sidebar');
        if (changePwBtn) {
            changePwBtn.addEventListener('click', openPasswordModal);
        }
    });

    function submitChangePasswordGlobal() {
        const form = document.getElementById('change-password-form-global');
        const formData = new FormData(form);

        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');

        if (newPassword !== confirmPassword) {
            alert('新密碼與確認密碼不符');
            return;
        }

        const data = {
            action: 'change_password',
            current_password: formData.get('current_password'),
            new_password: newPassword
        };

        fetch('user_management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('密碼修改成功');
                    closePasswordModal();
                    form.reset();
                } else {
                    alert('密碼修改失敗: ' + (data.error || '未知錯誤'));
                }
            })
            .catch(err => alert('發生錯誤: ' + err));
    }
</script>