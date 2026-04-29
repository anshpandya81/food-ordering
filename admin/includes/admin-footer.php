    </div><!-- end .admin-content -->
</div><!-- end .admin-main -->
</div><!-- end .admin-layout -->

<script>
// Confirm delete
function confirmDelete(url, itemName) {
    if (confirm('Delete "' + itemName + '"?\nThis action cannot be undone.')) {
        window.location.href = url;
    }
}

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.5s';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);

// Image preview on file input
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById(previewId);
            if (prev) {
                prev.innerHTML = '<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover;">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
