<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hello from Laravel!</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Material Design Bootstrap (MDB) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
</head>
<body style="background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif;">

  <div class="container my-5">
    <div class="card shadow-lg rounded-4" style="max-width: 600px; margin: auto;">
      <div class="card-body p-4">
        <!-- Header -->
        <div class="text-center mb-4">
          <h2 class="text-primary fw-bold">DQ Restaurant</h2>
          <p class="text-muted">Restaurant ERP Notification</p>
        </div>

        <!-- Title -->
        <h4 class="fw-semibold text-dark mb-3">{{ $details['title'] }}</h4>

        <!-- Body -->
        <p class="text-secondary fs-6">
          {{ $details['body'] }}
        </p>

        <!-- Button -->
        <div class="text-center mt-4">
          <a href="{{ config('app.url') }}" class="btn btn-primary btn-rounded px-4 py-2 shadow-2">
            Visit Website
          </a>
        </div>

        <!-- Divider -->
        <hr class="my-4">

        <!-- Footer -->
        <p class="text-muted text-center mb-0 small">
          Thanks,<br>
          <strong>{{ config('app.name') }}</strong><br>
          <a href="mailto:contact@dq.com" class="text-decoration-none text-primary">contact@dq.com</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>
