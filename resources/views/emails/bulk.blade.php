<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DQ Restaurant Notification</title>
</head>
<body style="background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 20px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto;">
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 24px;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 16px; text-align: center;">
                            <h2 style="font-size: 24px; font-weight: bold; color: #0d6efd; margin: 0;">DQ Restaurant</h2>
                            <p style="font-size: 14px; color: #6c757d; margin: 4px 0 0;">Restaurant ERP Notification</p>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style="padding-bottom: 12px;">
                            <h4 style="font-size: 18px; font-weight: 600; color: #212529; margin: 0;">{{ $details['title'] }}</h4>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding-bottom: 16px;">
                            <p style="font-size: 14px; color: #6c757d; margin: 0; line-height: 1.5;">{{ $details['body'] }}</p>
                        </td>
                    </tr>

                    <!-- Button -->
                    <tr>
                        <td align="center" style="padding-bottom: 16px;">
                            <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #0d6efd; color: #ffffff; font-size: 14px; font-weight: 500; text-decoration: none; padding: 8px 16px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Visit Website</a>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 16px 0;">
                            <hr style="border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="text-align: center;">
                            <p style="font-size: 12px; color: #6c757d; margin: 0;">Thanks,<br><strong>{{ config('app.name') }}</strong><br><a href="mailto:contact@dq.com" style="color: #0d6efd; text-decoration: none;">contact@dq.com</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
