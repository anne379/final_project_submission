const favoriteButtons = document.querySelectorAll('.favorite-btn');

favoriteButtons.forEach(button => {
    button.addEventListener('click', function() {
        const recipeId = this.getAttribute('data-recipe-id');
        const wasActive = this.classList.contains('active');
        
        // Toggle visual state immediately
        this.classList.toggle('active');
        this.innerHTML = this.classList.contains('active') 
            ? '<i class="fas fa-heart"></i>' 
            : '<i class="far fa-heart"></i>';
        
        // Send AJAX request
        fetch('favorite.php?recipeId=' + recipeId)
            .then(response => response.text())
            .then(data => {
                switch(data) {
                    case 'added':
                        // Successfully added - visual state is already correct
                        break;
                    case 'removed':
                        // Successfully removed - visual state is already correct
                        break;
                    case 'not_logged_in':
                        // Revert visual state and redirect to login
                        this.classList.toggle('active');
                        this.innerHTML = wasActive 
                            ? '<i class="fas fa-heart"></i>' 
                            : '<i class="far fa-heart"></i>';
                        window.location.href = 'login.php';
                        break;
                    default:
                        // Error - revert visual state
                        this.classList.toggle('active');
                        this.innerHTML = wasActive 
                            ? '<i class="fas fa-heart"></i>' 
                            : '<i class="far fa-heart"></i>';
                        alert('Error updating favorites!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert visual state on network error
                this.classList.toggle('active');
                this.innerHTML = wasActive 
                    ? '<i class="fas fa-heart"></i>' 
                    : '<i class="far fa-heart"></i>';
                alert('Network error! Please try again.');
            });
    });
});