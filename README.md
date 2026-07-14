# SmartCamp - Attendance Monitoring System

SmartCamp is a secure web-based attendance monitoring system designed to eliminate proxy attendance using a dual-verification mechanism. Instead of relying on a single QR code or PIN, SmartCamp requires students to verify their attendance using both a dynamically generated QR code and a time-bound 4-digit PIN that refresh every 4–6 seconds. This ensures that only students who are physically present in the classroom can successfully mark their attendance.

---

## Features

- Dual authentication using QR Code and 4-digit PIN
- QR Code and PIN refresh every 4–6 seconds
- Server-side validation and expiry checking
- Prevents duplicate attendance submissions
- Teacher dashboard for attendance management
- Live attendance monitoring
- Responsive web interface
- Lightweight JSON/TXT file storage (No database required)
- Fast and easy deployment

---

## Technology Stack

### Frontend
- HTML5
- Tailwind CSS
- JavaScript (Vanilla)

### Backend
- PHP

### Server
- Apache (XAMPP)

### Storage
- JSON
- TXT

---

## How It Works

1. The teacher creates an attendance session.
2. The system generates:
   - A unique QR Code
   - A random 4-digit PIN
3. Both credentials refresh automatically every 4–6 seconds.
4. Students scan the QR Code and enter the PIN.
5. The server verifies:
   - QR Code
   - PIN
   - Credential expiry
   - Student enrollment
   - Duplicate attendance attempts
6. Attendance is recorded only if all validations are successful.

---

## Project Structure

```text
smartcamp/
│
├── assets/
├── css/
├── js/
├── pages/
├── api/
├── data/
├── includes/
├── uploads/
├── index.php
└── README.md
```

---

## Installation

### Clone the repository

```bash
git clone https://github.com/your-username/smartcamp.git
```

### Move the project to your web server

Example (XAMPP):

```text
C:\xampp\htdocs\smartcamp
```

### Start Apache

Start the Apache service using XAMPP.

### Open in your browser

```text
http://localhost/smartcamp
```

---

## Requirements

- PHP 8.0 or later
- Apache Web Server
- XAMPP (Recommended)
- Modern Web Browser

---

## Security Features

- Dynamic QR Code generation
- Time-bound 4-digit PIN
- Server-side expiry validation
- One-time attendance recording
- Duplicate attendance prevention
- No dependency on the client-side clock

---

## Objectives

- Eliminate proxy attendance
- Ensure students are physically present during attendance
- Provide secure and transparent attendance verification
- Offer an easy-to-use teacher dashboard
- Maintain a lightweight system without requiring a database

---

## Future Enhancements

- MySQL database integration
- Face recognition
- RFID/NFC integration
- Mobile application
- Attendance reports
- Analytics dashboard
- Email notifications

---

## License

This project was developed for academic and educational purposes.

---

## Author

Developed as part of the **SmartCamp Attendance Monitoring System** project.
