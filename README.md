# Resume Builder

A web-based resume builder application that helps users create professional resumes with skill analysis and job recommendations using machine learning.

## Features

- User authentication (login/register)
- Resume creation and management
- Multiple resume templates
- Skill analysis using decision tree algorithm
- Job title recommendations based on skills
- PDF export functionality
- Admin panel for template management
- Responsive design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Python 3.8 or higher
- Composer
- XAMPP (or similar local development environment)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/resume-builder.git
cd resume-builder
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Python dependencies:
```bash
pip install -r requirements.txt
```

4. Create a MySQL database and import the schema:
```bash
mysql -u root -p < database.sql
```

5. Configure the database connection in `include/config.php`

6. Start the Python API server:
```bash
python python/skill_analyzer.py
```

7. Start XAMPP (Apache and MySQL)

8. Access the application at `http://localhost/resume_builder`

## Default Admin Account

- Username: admin
- Password: password

## Project Structure

```
resume_builder/
├── admin/              # Admin panel files
├── auth/              # Authentication files
├── include/           # PHP includes and configuration
├── python/            # Python API and ML model
├── data/              # Dataset files
├── assets/            # CSS, JS, and other assets
├── templates/         # Resume templates
└── vendor/            # Composer dependencies
```

## Technologies Used

- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript
- Backend: PHP 7.4
- Database: MySQL
- Machine Learning: Python, scikit-learn
- PDF Generation: TCPDF
- API: Flask (Python)

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 