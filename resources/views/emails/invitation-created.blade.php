<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} Invitation</title>
</head>
<body style="margin: 0; padding: 24px; background: #eef2ff; font-family: Arial, sans-serif; color: #0f172a;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        {{ $invitation->flatshare->owner->name }} invited you to join {{ $invitation->flatshare->name }} on {{ $appName }}.
    </div>

    <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; border: 1px solid #dbe4ff; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);">
        <div style="padding: 40px 32px; background: radial-gradient(circle at top left, rgba(125,211,252,0.35), transparent 36%), linear-gradient(135deg, #0f172a, #1d4ed8); color: #ffffff;">
            <p style="margin: 0; font-size: 12px; letter-spacing: 0.28em; text-transform: uppercase; color: #bfdbfe; font-weight: 700;">{{ $appName }}</p>
            <h1 style="margin: 18px 0 12px; font-size: 32px; line-height: 1.15; color: #ffffff;">
                Join {{ $invitation->flatshare->name }}
            </h1>
            <p style="margin: 0; font-size: 16px; line-height: 1.7; color: #dbeafe;">
                {{ $invitation->flatshare->owner->name }} invited <strong style="color:#ffffff;">{{ $invitation->email }}</strong> to join this flatshare.
            </p>
        </div>

        <div style="padding: 32px;">
            <div style="padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 18px;">
                <p style="margin: 0 0 8px; font-size: 14px; color: #475569; font-weight: 700;">What happens next</p>
                <p style="margin: 0; font-size: 15px; line-height: 1.7; color: #334155;">
                    If you already have an account, you will go to the login page. If you are new, you will see the registration page. After that, the invitation is applied and you can enter the flatshare.
                </p>
            </div>

            <p style="margin: 24px 0 0; font-size: 16px; line-height: 1.7; color: #334155;">
                Open the invitation to continue with the same email address and finish joining in one flow.
            </p>

            <p style="margin: 24px 0 28px;">
                <a href="{{ $acceptUrl }}" style="display: inline-block; padding: 15px 26px; border-radius: 999px; background: #0f172a; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 700;">
                    Open invitation
                </a>
            </p>

            <div style="display: block; margin: 0 0 24px; padding: 20px; border-radius: 18px; background: linear-gradient(180deg, #ffffff, #f8fafc); border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 14px; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; color: #64748b; font-weight: 700;">Invitation details</p>
                <p style="margin: 0 0 10px; font-size: 15px; color: #0f172a;">Flatshare: <strong>{{ $invitation->flatshare->name }}</strong></p>
                <p style="margin: 0 0 10px; font-size: 15px; color: #0f172a;">Owner: <strong>{{ $invitation->flatshare->owner->name }}</strong></p>
                <p style="margin: 0 0 10px; font-size: 15px; color: #0f172a;">Invited email: <strong>{{ $invitation->email }}</strong></p>
                <p style="margin: 0; font-size: 15px; color: #0f172a;">Expires: <strong>{{ $invitation->expires_at->format('d/m/Y H:i') }}</strong></p>
            </div>

            <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">If the button does not work, use this link:</p>
            <p style="margin: 0; word-break: break-all;">
                <a href="{{ $acceptUrl }}" style="color: #2563eb; text-decoration: underline;">{{ $acceptUrl }}</a>
            </p>
        </div>
    </div>
</body>
</html>
