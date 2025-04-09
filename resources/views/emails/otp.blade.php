<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP for DQ Restaurant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-lg shadow-lg p-6">
            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">DQ Restaurant Management System</h1>
                <p class="text-gray-600 mt-1">Your One-Time Password (OTP)</p>
            </div>

            <!-- OTP Section -->
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-lg text-gray-700 mb-2">Your OTP for login is:</p>
                <p class="text-3xl font-semibold text-blue-600">{{ $otp }}</p>
            </div>

            <!-- Instructions -->
            <div class="mt-6 text-gray-600 text-center">
                <p>Please use this OTP to complete your login at <a href="https://letsdq.com" class="text-blue-500 hover:underline">letsdq.com</a>.</p>
                <p class="mt-2">This OTP is valid for 10 minutes. Do not share it with anyone.</p>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>&copy; 2025 DQ Restaurant Management System. All rights reserved.</p>
                <p>Questions? Contact us at <a href="mailto:support@letsdq.com" class="text-blue-500 hover:underline">support@letsdq.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
