# Veritick - Secure Event Ticketing & Management System

Veritick is a professional-grade web application designed to streamline event organization. It handles everything from event discovery to secure, one-time-use QR entry validation.

## 🚀 Key Features
* **Smart Ticketing:** Prevents duplicate ticket generation; one user, one unique QR code.
* **Live Admin Scanner:** Integrated camera access for real-time QR validation at the venue.
* **Attendance Analytics:** Live dashboard for admins to track "Checked-in" vs "Expected" guests.
* **Document Management:** Secure upload and sync of Aadhar/Agreements between Admin and Renter/User profiles.
* **Mobile Optimized:** Fully responsive UI with no horizontal scrolling on key management grids.

## 🛠️ Tech Stack
* **Frontend:** [Insert e.g., React.js / Vue.js]
* **Backend:** [Insert e.g., Node.js / Python Django]
* **Database:** [Insert e.g., PostgreSQL / MongoDB]
* **QR Logic:** [Insert e.g., Html5-Qrcode / QRCode.js]

## 📸 System Logic & Workflow
1. **User Side:** Browse Events -> Register -> Input Details -> Generate Secure QR.
2. **Admin Side:** Open Scanner -> Scan User QR -> System validates `is_scanned` status -> Access Granted/Denied.
3. **Data Integrity:** Once Admin uploads official docs, they are locked for the User to prevent tampering.
