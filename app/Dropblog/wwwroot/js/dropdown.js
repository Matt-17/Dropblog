window.addClickOutsideHandler = (dropdownClass) => {
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.' + dropdownClass);
        if (dropdown && !dropdown.contains(event.target)) {
            // Trigger a custom event that Blazor can listen to
            const closeEvent = new CustomEvent('closeDropdown');
            document.dispatchEvent(closeEvent);
        }
    });
}; 