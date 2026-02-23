<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="confirmationModalLabel">
          <i class="bi bi-exclamation-triangle text-warning"></i>
          Confirm Action
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="confirmationMessage" class="mb-0">Are you sure you want to proceed with this action?</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmActionBtn">
          <i class="bi bi-check-circle"></i> Confirm
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.modal-content {
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-xl);
}

.modal-header {
  padding: var(--space-lg) var(--space-xl);
}

.modal-body {
  padding: var(--space-md) var(--space-xl) var(--space-lg);
  color: var(--text-secondary);
  font-size: var(--text-base);
}

.modal-footer {
  padding: var(--space-md) var(--space-xl) var(--space-lg);
  gap: var(--space-sm);
}

.modal-title {
  font-size: var(--text-lg);
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}

.modal-title i {
  font-size: 1.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
  const confirmActionBtn = document.getElementById('confirmActionBtn');
  const confirmationMessage = document.getElementById('confirmationMessage');
  let pendingAction = null;

  // Global function to show confirmation
  window.showConfirmation = function(message, callback, options = {}) {
    confirmationMessage.textContent = message;
    
    // Update button text and style if provided
    if (options.confirmText) {
      confirmActionBtn.innerHTML = `<i class="bi bi-check-circle"></i> ${options.confirmText}`;
    } else {
      confirmActionBtn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm';
    }
    
    if (options.danger) {
      confirmActionBtn.className = 'btn btn-danger';
    } else {
      confirmActionBtn.className = 'btn btn-primary';
    }
    
    pendingAction = callback;
    confirmationModal.show();
  };

  // Handle confirmation
  confirmActionBtn.addEventListener('click', function() {
    if (pendingAction) {
      pendingAction();
      pendingAction = null;
    }
    confirmationModal.hide();
  });

  // Reset on modal close
  document.getElementById('confirmationModal').addEventListener('hidden.bs.modal', function() {
    pendingAction = null;
  });

  // Add confirmation to all elements with data-confirm attribute
  document.addEventListener('click', function(e) {
    const target = e.target.closest('[data-confirm]');
    if (target) {
      e.preventDefault();
      e.stopPropagation();
      
      const message = target.getAttribute('data-confirm');
      const confirmText = target.getAttribute('data-confirm-text') || 'Confirm';
      const isDanger = target.getAttribute('data-confirm-danger') === 'true';
      
      showConfirmation(message, function() {
        // If it's a form button, submit the form
        if (target.tagName === 'BUTTON' && target.type === 'submit') {
          const form = target.closest('form');
          if (form) {
            // Remove the data-confirm attribute temporarily to avoid loop
            target.removeAttribute('data-confirm');
            form.submit();
          }
        }
        // If it's a link, navigate to it
        else if (target.tagName === 'A') {
          window.location.href = target.href;
        }
        // If it has onclick, execute it
        else if (target.hasAttribute('onclick')) {
          eval(target.getAttribute('onclick'));
        }
      }, {
        confirmText: confirmText,
        danger: isDanger
      });
    }
  });
});
</script>
