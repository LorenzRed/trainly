<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Trainly | Profile Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 0% 0%, #122017 0%, #07130b 45%, #050c08 100%);
            color: #f0f3f8;
            min-height: 100vh;
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 24px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .back-link {
            color: #fde68a;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .card {
            background: rgba(15, 27, 19, 0.92);
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 18px 32px rgba(0, 0, 0, 0.28);
        }

        .card h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .muted {
            color: #9aa4b8;
            margin-bottom: 24px;
        }

        .save-toast {
            position: fixed;
            top: 22px;
            right: 22px;
            background: #16a34a;
            color: #ecfdf5;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 10px 14px;
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.24);
            font-size: 0.92rem;
            font-weight: 700;
            z-index: 2200;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .save-toast.hidden {
            opacity: 0;
            transform: translateY(-6px);
            pointer-events: none;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            color: #cfd9ed;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .field input {
            background: #07130b;
            border: 1px solid #1f2b22;
            color: #f0f3f8;
            border-radius: 12px;
            padding: 12px 14px;
            outline: none;
            text-transform: uppercase;
        }

        .field input:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.18);
        }

        .field input[readonly] {
            background: #0b1810;
            color: #9aa4b8;
            cursor: not-allowed;
        }

        .readonly {
            color: #fcd34d;
            font-size: 0.9rem;
        }

        .error {
            color: #fca5a5;
            font-size: 0.82rem;
        }

        .field-note {
            color: #9aa4b8;
            font-size: 0.8rem;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 999px;
            padding: 12px 22px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(95deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(251, 191, 36, 0.55);
            color: #fde68a;
        }

        @media (max-width: 760px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @if(session('status'))
        <div class="save-toast" id="saveToast">Saved changes</div>
    @endif

    <div class="container">
        <div class="top-bar">
            <a href="{{ route('admin.main') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>

        <div class="card">
            <h1>Profile Settings</h1>
            <p class="muted">Update your account details and keep your information up to date.</p>

            <form action="{{ route('admin.profile.update') }}" method="post">
                @csrf
                @method('PUT')

                <div class="grid">
                    <div class="field">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="business_name">Business Name</label>
                        <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $user->business_name) }}" placeholder="Enter business name">
                        @error('business_name')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ $user->email }}" readonly>
                        <p class="field-note">Email cannot be changed.</p>
                    </div>

                    <div class="field">
                        <label for="location">Location</label>
                        <input id="location" name="location" type="text" value="{{ old('location', $user->location) }}" placeholder="Enter your location">
                        @error('location')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="contact_number">Contact Number</label>
                        <input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number', $user->contact_number) }}" placeholder="Enter your contact number">
                        @error('contact_number')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">New Password (optional)</label>
                        <input id="password" name="password" type="password" placeholder="Leave blank to keep current password">
                        @error('password')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm new password">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.main') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const saveToast = document.getElementById('saveToast');

        if (saveToast) {
            setTimeout(function () {
                saveToast.classList.add('hidden');
            }, 1000);
        }
    </script>
</body>

</html>
