import "./bootstrap";
import "./livewire-toaster";

// Apply theme immediately on page load
function applyTheme() {
    const savedTheme = localStorage.getItem('theme') || 'default';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Handle dark mode class for themes that might need it
    if (savedTheme === 'dark' || savedTheme === 'night' || savedTheme === 'coffee' || savedTheme === 'forest' || savedTheme === 'luxury' || savedTheme === 'synthwave') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Apply theme immediately before any JS runs
applyTheme();

// Theme Controller Implementation
function initializeTheme() {
    // Get theme from localStorage or use default
    const savedTheme = localStorage.getItem('theme') || 'default';
    
    // Apply the saved theme on page load (redundant but ensures consistency)
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Handle dark mode class
    if (savedTheme === 'dark' || savedTheme === 'night' || savedTheme === 'coffee' || savedTheme === 'forest' || savedTheme === 'luxury' || savedTheme === 'synthwave') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // Select all theme controller radio buttons
    const themeControllers = document.querySelectorAll('.theme-controller');
    
    // Set the correct radio button based on current theme
    themeControllers.forEach(controller => {
        if (controller.value === savedTheme) {
            controller.checked = true;
        }
        
        // Add change event listener to each radio button
        controller.addEventListener('change', function(event) {
            const newTheme = event.target.value;
            
            // Update the document theme
            document.documentElement.setAttribute('data-theme', newTheme);
            
            // Handle dark mode class
            if (newTheme === 'dark' || newTheme === 'night' || newTheme === 'coffee' || newTheme === 'forest' || newTheme === 'luxury' || newTheme === 'synthwave') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            // Save to localStorage for persistence across pages
            localStorage.setItem('theme', newTheme);
        });
    });
}

// Listen for navigating events (before page transition)
document.addEventListener('livewire:navigating', applyTheme);
document.addEventListener('alpine:navigating', applyTheme);

// Initialize theme on initial page load
document.addEventListener('DOMContentLoaded', initializeTheme);

// Re-initialize theme after navigation completes
document.addEventListener('livewire:navigated', initializeTheme);
document.addEventListener('alpine:navigated', initializeTheme);
