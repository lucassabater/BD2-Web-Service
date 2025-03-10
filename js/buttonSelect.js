
// JavaScript to handle category button click and filter products
const categoryButtons = document.querySelectorAll('.category-button');
const products = document.querySelectorAll('.product');

categoryButtons.forEach(button => {
  button.addEventListener('click', function() {
    // Remove 'active' class from all buttons
    categoryButtons.forEach(b => b.classList.remove('active'));

    // Add 'active' class to the clicked button
    this.classList.add('active');

    // Get the category selected
    const selectedCategory = this.getAttribute('data-category');
    window.location.href = `?category=${selectedCategory}`;
    // Filter products based on the selected category
    products.forEach(product => {
      if (selectedCategory === 'all') {
        product.style.display = 'block';  // Show all
      } else if (product.classList.contains(selectedCategory)) {
        product.style.display = 'block';  // Show matching category
      } else {
        product.style.display = 'none';  // Hide non-matching category
      }
    });
  });
});



