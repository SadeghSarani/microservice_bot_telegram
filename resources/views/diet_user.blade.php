<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Diet Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        @font-face {
            font-family: 'Azarmehr';
            src: url('/fonts/AzarMehr/AzarMehr-Medium.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Azarmehr', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #e0f7fa, #e8f5e9);
            color: #2e7d32;
        }

        .container {
            max-width: 794px;
            width: 100%;
            margin: 40px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 32px;
        }

        h1, h2, h3, span {
            font-family: 'Azarmehr', sans-serif;
        }

        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }

        .logo-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 20px;
            justify-content: center;
        }

        .logo-png {
            width: 50px;
            height: auto;
            display: block;
        }

        .btn-download {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-download:hover {
            background-color: #388e3c;
        }

        .user-info {
            margin-top: 24px;
            direction: rtl;
        }

        .user-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-info td {
            padding: 8px 12px;
            border-bottom: 1px solid #c8e6c9;
        }

        .photo-log {
            margin-top: 32px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
        }

        .photo-grid img {
            width: 100%;
            height: auto;
            border-radius: 12px;
            border: 3px solid #aed581;
        }

        @media (min-width: 600px) {
            .header {
                flex-direction: row;
            }
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .btn-download {
                display: none;
            }

            .container {
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container" id="dietPage">
    <div class="header">
        <div class="logo-group">
            <img src="/photo/logo.png" class="logo-png" alt="Logo">
            <img src="/photo/logo_text_1.png" class="logo-png" alt="Logo Text">
        </div>

        <button class="btn-download" id="downloadBtn" onclick="downloadPDF()">
            <span>دانلود فایل</span> PDF
        </button>
    </div>

    <div class="user-info">
        {!! $content !!}
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.getElementById('dietPage');
        const button = document.getElementById('downloadBtn');
        button.style.display = 'none';

        const opt = {
            margin: 0,
            filename: 'user-diet.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                scrollY: 0
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            },
            pagebreak: { mode: ['css', 'legacy', 'avoid-all'] }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            button.style.display = 'block';
        });
    }
</script>

</body>
</html>
