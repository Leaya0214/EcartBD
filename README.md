<h1 align="center">EcartBd</h1>

<h2>🎯 Overview</h2>
<p>
This project is a comprehensive <strong>E-commerce Platform</strong> developed to provide a seamless online shopping experience. 
It encompasses essential e-commerce functionalities including robust product management, an intuitive shopping cart system, 
streamlined order processing, and secure payment gateway integration. Built with a focus on clean architecture and user experience, 
this platform aims to serve as a complete solution for online retail.
</p>

<h2>✨ Features</h2>
<ul>
  <li><strong>User Management:</strong> Secure user registration, login, and profile management.</li>
  <li><strong>Product Catalog:</strong> Comprehensive product listing with categories, search, filtering, and detailed product pages.</li>
  <li><strong>Shopping Cart & Checkout:</strong> Intuitive add-to-cart functionality and a streamlined checkout process.</li>
  <li><strong>Secure Payment Integration:</strong> Seamless integration with <em>Stripe, PayPal, SSLCommerz</em> for secure online transactions.</li>
  <li><strong>Order Management:</strong> Admin panel for tracking orders, updating statuses, and managing customer inquiries.</li>
  <li><strong>SMS Integration:</strong> Automated SMS notifications for order confirmations, shipping updates, etc. (e.g., using Twilio, custom SMS gateway).</li>
  <li><strong>Social App Integration:</strong> Login with Google/Facebook, or sharing product links directly to social apps via integration.</li>
  <li><strong>Admin Dashboard:</strong> Intuitive interface for managing products, orders, users, and site settings.</li>
  <li><strong>Clean Code & Optimization:</strong> Emphasis on maintainable code and optimized database queries for performance.</li>
  <li><strong>Responsive Design:</strong> Optimized for various devices (desktop, tablet, mobile) using Bootstrap.</li>
</ul>

<h2>🛠️ Technologies Used</h2>

<h3>Backend</h3>
<ul>
  <li><strong>PHP:</strong> 8.1</li>
  <li><strong>Laravel Framework:</strong> 8</li>
  <li><strong>Database:</strong> MySQL</li>
</ul>

<h3>Frontend</h3>
<ul>
  <li>JavaScript</li>
  <li><strong>Bootstrap:</strong> 5.x</li>
</ul>

<h3>Tools & Environment</h3>
<ul>
  <li><strong>Version Control:</strong> Git, GitHub</li>
  <li><strong>Local Development:</strong> Laragon / XAMPP</li>
  <li><strong>API Testing:</strong> Postman</li>
  <li><strong>Server Management (Deployment):</strong> AWS EC2</li>
</ul>

<h2>🚀 Getting Started</h2>

<h3>Prerequisites</h3>
<p>Before you begin, ensure you have the following installed on your system:</p>
<ul>
  <li>PHP 8.1 or higher</li>
  <li>Composer</li>
  <li>MySQL Server</li>
  <li>Node.js & npm (if using frontend assets like React, or compiling CSS/JS)</li>
  <li>Git</li>
</ul>

<h3>Installation</h3>
<ol>
  <li>Clone the repository: <code>git clone https://github.com/Leaya0214/Ecommerce-eShop.git</code></li>
  <li>Install PHP dependencies: <code>composer install</code></li>
  <li>Create .env file and generate application key:
    <pre><code>cp .env.example .env
php artisan key:generate</code></pre>
  </li>
  <li>Configure your .env file: Update Database and other modules as per your setup.</li>
  <li>Run database migrations: <code>php artisan migrate</code></li>
  <li>Start the local development server: <code>php artisan serve</code></li>
</ol>

<p>🎉 Your application should now be accessible at <a href="http://localhost:8000">http://localhost:8000</a> (or the URL specified by <code>php artisan serve</code>).</p>

<h2>🎉 Conclusion</h2>
<p>
This <strong>E-commerce Platform</strong> project represents a significant learning journey in building full-featured web applications using Laravel. 
It demonstrates robust backend logic, secure third-party integrations, and an emphasis on a smooth user experience. 
This project serves as a testament to my ability to develop comprehensive solutions, handle complex system requirements, and continuously apply best practices in software engineering. 
I am proud of the features implemented and the challenges overcome during its development.
</p>
