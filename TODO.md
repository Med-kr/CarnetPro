# Task: Update Invitation Email for EasyColoc

## Plan:

### 1. Update Email Template ✅
- [x] Update `resources/views/emails/invitation.blade.php` to match EasyColoc format:
  - EasyColoc branding and logo
  - "Colocation: {name}" label
  - "Invité par {owner_name} · {address}" 
  - Expiration date in French format "Expire le {date} à {time}"
  - Accept/Refuse buttons
  - Fallback link

### 2. Update Mail Class ✅
- [x] Update `app/Mail/FlatshareInvitationMail.php`:
  - Change subject to "🏠 EasyColoc - Invitation à rejoindre une colocation"

### 3. Update InvitationService ✅
- [x] Ensure the invitation is loaded with flatshare.owner relationship

