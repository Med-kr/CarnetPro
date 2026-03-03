<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} Invitation</title>
</head>
<body style="margin:0;padding:24px;background:#eef2ff;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #dbe4ff;">
        <div style="padding:32px;background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#ffffff;">
            <p style="margin:0;font-size:12px;letter-spacing:0.28em;text-transform:uppercase;color:#bfdbfe;font-weight:700;">{{ $appName }}</p>
            <h1 style="margin:18px 0 12px;font-size:32px;line-height:1.15;color:#ffffff;">
                Join {{ $invitation->flatshare->name }}
            </h1>
            <p style="margin:0;font-size:16px;line-height:1.7;color:#dbeafe;">
                {{ $invitation->flatshare->owner->name }} invited <strong style="color:#ffffff;">{{ $invitation->email }}</strong> to join this flatshare.
            </p>
        </div>

        <div style="padding:32px;">
            <p style="margin:0 0 20px;font-size:16px;line-height:1.7;color:#334155;">
                Open the invitation to continue with the same email address and finish joining in one flow.
            </p>

            <p style="margin:24px 0 28px;">
                <a href="{{ $acceptUrl }}" style="display:inline-block;padding:15px 26px;border-radius:999px;background:#0f172a;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;">
                    Open invitation
                </a>
            </p>

            <p style="margin:0 0 8px;font-size:13px;color:#64748b;">If the button does not work, use this link:</p>
            <p style="margin:0;word-break:break-all;">
                <a href="{{ $acceptUrl }}" style="color:#2563eb;text-decoration:underline;">{{ $acceptUrl }}</a>
            </p>
        </div>
    </div>
</body>
</html>
