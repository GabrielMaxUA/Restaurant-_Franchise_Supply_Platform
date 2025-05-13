document.addEventListener('DOMContentLoaded', function() {
    // Create the loading overlay element
    const overlayHTML = `
        <div id="loading-overlay" class="loading-overlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-message">Processing your request...</div>
            </div>
        </div>
    `;
    
    // Append the overlay to the body
    document.body.insertAdjacentHTML('beforeend', overlayHTML);
    
    // Get the overlay element
    const overlay = document.getElementById('loading-overlay');
    
    // Find all forms in the document
    const forms = document.querySelectorAll('form:not([data-no-loading])');
    
    // Add submit event listener to each form
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check if the form has any file inputs
            const fileInputs = form.querySelectorAll('input[type="file"]');
            let hasFiles = false;
            
            // Check if any file inputs have files selected
            fileInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    hasFiles = true;
                }
            });
            
            // Only show loading overlay if form is valid
            if (form.checkValidity()) {
                // Show the loading overlay
                overlay.classList.add('active');
                
                // Add additional message for file uploads
                if (hasFiles) {
                    document.querySelector('.loading-message').textContent = 'Uploading files. This may take a moment...';
                } else {
                    document.querySelector('.loading-message').textContent = 'Processing your request...';
                }
                
                // Disable submit button to prevent double submissions
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitButtons.forEach(button => {
                    button.disabled = true;
                });
            }
        });
    });
    
    // Handle back button usage (to hide overlay if user navigates back)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache (back button)
            overlay.classList.remove('active');
            
            // Re-enable all submit buttons
            const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = false;
            });
        }
    });
});