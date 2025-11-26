// Initialize DataTables
$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        }
    });
});

// Show success message using SweetAlert2
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

// Show error message using SweetAlert2
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message
    });
}

// Show confirmation dialog using SweetAlert2
function showConfirm(message, callback) {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    $('body').append(toast);
    const toastElement = $('.toast').last();
    const bsToast = new bootstrap.Toast(toastElement);
    bsToast.show();
    
    toastElement.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'KES'
    }).format(amount);
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Handle form submission with AJAX
function handleFormSubmit(formId, successCallback) {
    $(`#${formId}`).on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    if (successCallback) {
                        successCallback(response);
                    }
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('An error occurred. Please try again.');
            }
        });
    });
}

// Print element
function printElement(elementId) {
    const element = document.getElementById(elementId);
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = element.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;
    
    // Reinitialize any necessary scripts
    location.reload();
} 