# Security Policy

## Reporting a Vulnerability

Rentra handles payment proofs and personal data (names and phone numbers of renters). If you find a security issue, do not open a public issue.

Report privately by email to nicolasdwidharma@gmail.com with the subject `[RENTA SECURITY]`. Include:

- A description of the issue and its impact
- Steps to reproduce, or a proof of concept
- The affected version or commit, if known

You will get an acknowledgement within 7 days. We ask that you give us time to fix the issue before public disclosure.

## Scope

- Authentication and session handling
- Tenant isolation between rental accounts
- File upload validation and storage of payment proofs
- Authorization on owner-only routes (dashboard, DP verification, proof download)
- Rate limiting on public booking and upload endpoints

## Out of Scope

- Social engineering of the owner or renters
- Denial of service against a self-hosted instance
- Issues in third-party services (XAMPP, WhatsApp)
