<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Room Not Found | Escapy</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        .error-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }

        .error-code {
            font-family: 'Cormorant Garamond', serif;
            font-size: 10rem;
            font-weight: 700;
            color: #cc3333;
            line-height: 1;
            letter-spacing: 0.05em;
            opacity: 0.15;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -60%);
            user-select: none;
            pointer-events: none;
        }

        .error-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .error-eyebrow {
            font-family: 'Jost', sans-serif;
            font-size: 0.72rem;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: #cc3333;
            margin-bottom: 1.5rem;
            animation: fadeIn 0.8s ease both;
        }

        .error-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: #e8dcc8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.8s ease 0.2s both;
        }

        .error-divider {
            width: 60px;
            height: 1px;
            background: #cc3333;
            margin: 1.5rem auto;
            animation: fadeIn 0.8s ease 0.3s both;
        }

        .error-message {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            color: #7a8290;
            font-size: 0.9rem;
            line-height: 1.8;
            letter-spacing: 0.05em;
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.5s both;
        }

        .btn-error-primary {
            background: #cc3333;
            border: 1px solid #cc3333;
            color: #fff;
            font-family: 'Jost', sans-serif;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 12px 32px;
            border-radius: 0;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-error-primary:hover {
            background: #e04444;
            border-color: #e04444;
            color: #fff;
            text-decoration: none;
        }

        .btn-error-secondary {
            background: transparent;
            border: 1px solid #3d4450;
            color: #7a8290;
            font-family: 'Jost', sans-serif;
            font-size: 0.78rem;
            font-weight: 400;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 12px 32px;
            border-radius: 0;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-error-secondary:hover {
            border-color: #cc3333;
            color: #c8c0b0;
            text-decoration: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Flicker animation on the 404 number */
        @keyframes flicker {
            0%, 100% { opacity: 0.15; }
            92%       { opacity: 0.15; }
            93%       { opacity: 0.08; }
            94%       { opacity: 0.15; }
            96%       { opacity: 0.10; }
            97%       { opacity: 0.15; }
        }

        .error-code {
            animation: flicker 4s infinite;
        }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="error-page">
        <div class="error-code">404</div>
        <div class="error-content">
            <p class="error-eyebrow">— Error 404 —</p>
            <h1 class="error-title">Room Not Found</h1>
            <div class="error-divider"></div>
            <p class="error-message">
                The room you're looking for seems to have vanished.<br>
                Perhaps it was never there — or perhaps you were never meant to find it.
            </p>
            <div class="error-actions">
                <a href="index.php" class="btn-error-primary">Back to Home</a>
                <a href="index.php#Rooms" class="btn-error-secondary">Browse Rooms</a>
            </div>
        </div>
    </div>

    <?php include "inc/footer.inc.php"; ?>
</body>
</html>