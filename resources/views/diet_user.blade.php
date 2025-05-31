<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Diet Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- PDF Export JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>

        @font-face {
            font-family: 'Azarmehr';
            src: url('/fonts/AzarMehr/AzarMehr-Medium.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            #dietPage {
                width: 794px; /* A4 width in pixels at 96dpi */
                max-width: 100%;
                margin: 0 auto;
                font-size: 14px;
                transform: scale(1);
                transform-origin: top left;
            }

            .photo-grid {
                grid-template-columns: repeat(4, 1fr); /* force 4 columns for photo log */
            }
        }

        body {
            font-family: 'Azarmehr', sans-serif;
            margin: 0;
            background: linear-gradient(to bottom, #e0f7fa, #e8f5e9);
            color: #2e7d32;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 16px;
            padding: 32px;
        }

        h1, h2, h3, span {
             font-family: 'Azarmehr', sans-serif;
         }

        .user-info, .photo-log {
            margin-top: 24px;
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

        #dietPage {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        * {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .btn-download {
            display: block;
            margin: 30px auto 0;
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

        .logo-png {
            width: 50px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="container" id="dietPage">
    <img src="/photo/logo.png"  class="logo-png"/>

    <div class="user-info" style="direction: rtl" >
            {!! $content !!}
    </div>

    <button class="btn-download" onclick="downloadPDF()"><span>دانلود فایل</span> PDF</button>
</div>

<script>
    function downloadPDF() {
        const element = document.getElementById('dietPage');
        element.classList.add('pdf-export'); // if needed to apply special styles

        const opt = {
            margin:       0,
            filename:     'user-diet.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            element.classList.remove('pdf-export');
        });
    }
</script>

</body>
</html>
