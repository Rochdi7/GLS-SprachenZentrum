<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zertifikat {{ $certificate->exam_level }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@300;400;500&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }

        .certificate {
            max-width: 794px;
            margin: 0 auto;
            background: white;
            padding: 60px 80px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .certificate {
                padding: 40px 30px;
            }

            .header {
                flex-direction: column;
                gap: 20px;
                margin-bottom: 40px;
            }

            .organization {
                text-align: left;
            }

            .title h1 {
                font-size: 32px;
                letter-spacing: 6px;
            }

            .title h2 {
                font-size: 28px;
                letter-spacing: 5px;
            }

            .personal-info {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .scores-section {
                grid-template-columns: 1fr;
                gap: 10px !important;
            }

            .scores-column {
                align-items: center;
            }

            .result-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .result-value {
                text-align: center;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .signatures {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }

        @media screen and (max-width: 480px) {
            .certificate {
                padding: 30px 20px;
            }

            .title h1 {
                font-size: 28px;
                letter-spacing: 4px;
            }

            .title h2 {
                font-size: 24px;
                letter-spacing: 3px;
            }

            .logo {
                font-size: 36px;
            }

            .score-row {
                font-size: 13px;
            }

            .score-row.total {
                font-size: 15px;
            }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
        }

        .logo-section {
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-size: 48px;
            font-weight: 700;
            color: #3d5a6c;
            letter-spacing: 2px;
            line-height: 1;
        }

        .logo-subtitle {
            font-size: 8px;
            color: #7a7a7a;
            letter-spacing: 1.5px;
            margin-top: 4px;
            font-weight: 400;
        }

        .organization {
            text-align: right;
            font-size: 11px;
            color: #333;
            font-weight: 400;
            line-height: 1.5;
        }

        .title {
            text-align: center;
            margin-bottom: 60px;
        }

        .title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 400;
            letter-spacing: 12px;
            color: #2d2d2d;
            margin-bottom: 10px;
        }

        .title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 400;
            letter-spacing: 10px;
            color: #2d2d2d;
        }

        .personal-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            margin-bottom: 50px;
        }

        .info-column {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .info-field {
            position: relative;
        }

        .info-label {
            font-size: 11px;
            color: #666;
            font-weight: 400;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 14px;
            color: #000;
            font-weight: 400;
            letter-spacing: 1px;
            padding-bottom: 8px;
            border-bottom: 1px solid #d0d0d0;
        }

        .scores-section {
            display: grid;
            grid-template-columns: auto auto;
            gap: 40px;
            margin-bottom: 50px;
            align-items: start;
        }

        .exam-categories {
            text-align: center;
        }

        .exam-categories h3 {
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            color: #000;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .exam-categories ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .exam-categories li {
            font-size: 12px;
            color: #555;
            font-weight: 400;
            margin-bottom: 5px;
            padding-left: 0;
            padding-right: 0;
            position: relative;
            line-height: 1.3;
        }

        .exam-categories li::before {
            display: none;
        }

        .exam-categories .section-title {
            font-size: 13px;
            font-weight: 500;
            color: #000;
            margin: 10px 0 5px 0;
            padding-left: 0;
            line-height: 1.3;
            text-align: center;
        }

        .scores-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 0;
        }

        .score-row {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            font-weight: 400;
            color: #000;
            margin-bottom: 5px;
            white-space: nowrap;
            line-height: 1.3;
        }

        .score-row.total {
            font-weight: 500;
            font-size: 17px;
            margin-bottom: 5px;
        }

        .score-row.section-total {
            font-weight: 500;
            font-size: 15px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .score-number {
            display: inline-block;
            min-width: 42px;
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin-right: 0;
        }

        .score-row.total .score-number {
            border-bottom: 1px solid #000;
            font-weight: 500;
        }

        .score-row.section-total .score-number {
            border-bottom: 1px solid #000;
            font-weight: 500;
        }

        .score-separator {
            margin: 0 12px;
            font-weight: 400;
        }

        .score-max {
            min-width: 30px;
            text-align: center;
        }

        .result-section {
            display: grid;
            grid-template-columns: auto auto;
            gap: 20px;
            margin-bottom: 60px;
            justify-content: center;
            align-items: center;
        }

        .result-label {
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            color: #000;
            text-align: center;
        }

        .result-value {
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            color: #000;
            text-align: center;
        }

        .details-section {
            margin-bottom: 60px;
        }

        .details-column {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 280px auto;
            align-items: baseline;
            gap: 40px;
        }

        .detail-label {
            font-size: 11px;
            color: #666;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 13px;
            color: #000;
            font-weight: 500;
            letter-spacing: 0.8px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            margin-top: 80px;
        }

        .signature-line {
            border-top: 1px solid #c0c0c0;
            padding-top: 10px;
            position: relative;
        }

        .signature-image {
            position: absolute;
            bottom: 20px;
            left: 0;
            max-width: 150px;
            max-height: 50px;
            object-fit: contain;
        }

        .signature-label {
            font-size: 10px;
            color: #666;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .logo-image {
            width: 95px;
            height: auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="certificate">

        <!-- HEADER -->
        <div class="header">
            <div class="logo-section">
                <img src="{{ asset('assets/images/gls-noir.png') }}" alt="GLS Sprachenzentrum Logo" class="logo-image">
            </div>

            <div class="organization">
                GLS-Language & Integration<br>Center
            </div>
        </div>


        <!-- TITLE -->
        <div class="title">
            <h1>ZERTIFIKAT</h1>
            <h2>{{ strtoupper($certificate->exam_level) }}</h2>
        </div>


        <!-- PERSONAL INFORMATION -->
        <div class="personal-info">
            <div class="info-column">
                <div class="info-field">
                    <div class="info-label">Name</div>
                    <div class="info-value">{{ $certificate->last_name }}</div>
                </div>

                <div class="info-field">
                    <div class="info-label">Geburtsdatum</div>
                    <div class="info-value">{{ $certificate->birth_date->format('d.m.Y') }}</div>
                </div>
            </div>

            <div class="info-column">
                <div class="info-field">
                    <div class="info-label">Vorname</div>
                    <div class="info-value">{{ $certificate->first_name }}</div>
                </div>

                <div class="info-field">
                    <div class="info-label">Geburtsort</div>
                    <div class="info-value">{{ $certificate->birth_place }}</div>
                </div>
            </div>
        </div>


        <!-- SCORES -->
        <div class="scores-section">

            <!-- LEFT: CATEGORIES -->
            <div class="exam-categories">
                <h3>Schriftliche Prüfung</h3>
                <ul>
                    <li>Leseverstehen</li>
                    <li>Sprachbausteine</li>
                    <li>Hörverstehen</li>
                    <li>Schriftlicher Ausdruck</li>
                </ul>

                <div class="section-title">Mündliche Prüfung</div>
                <ul>
                    <li>Präsentation</li>
                    <li>Diskussion</li>
                    <li>Problemlösung</li>
                </ul>
            </div>


            <!-- RIGHT: NUMBERS -->
            <div class="scores-column">

                <!-- FULL TOTAL -->
                <div class="score-row total">
                    <span class="score-number">{{ $certificate->written_total + $certificate->oral_total }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">300</span>
                </div>

                <!-- WRITTEN SCORES -->
                <div class="score-row">
                    <span class="score-number">{{ $certificate->reading_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">75</span>
                </div>

                <div class="score-row">
                    <span class="score-number">{{ $certificate->grammar_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">30</span>
                </div>

                <div class="score-row">
                    <span class="score-number">{{ $certificate->listening_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">75</span>
                </div>

                <div class="score-row">
                    <span class="score-number">{{ $certificate->writing_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">45</span>
                </div>

                <!-- ORAL TOTAL -->
                <div class="score-row section-total">
                    <span class="score-number">{{ $certificate->oral_total }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">75</span>
                </div>

                <!-- ORAL SCORES -->
                <div class="score-row">
                    <span class="score-number">{{ $certificate->presentation_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">25</span>
                </div>

                <div class="score-row">
                    <span class="score-number">{{ $certificate->discussion_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">25</span>
                </div>

                <div class="score-row">
                    <span class="score-number">{{ $certificate->problemsolving_score }}</span>
                    <span class="score-separator">/</span>
                    <span class="score-max">25</span>
                </div>
            </div>

        </div>


        <!-- FINAL RESULT -->
        <div class="result-section">
            <div class="result-label">Ergebnis</div>
            <div class="result-value">{{ $certificate->final_result }}</div>
        </div>


        <!-- EXAM DETAILS -->
        <div class="details-section">
            <div class="details-column">

                <div class="detail-row">
                    <div class="detail-label">Datum der Prüfung</div>
                    <div class="detail-value">{{ $certificate->exam_date->format('d.m.Y') }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Datum der Ausstellung</div>
                    <div class="detail-value">{{ $certificate->issue_date->format('d.m.Y') }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Teilnehmernummer</div>
                    <div class="detail-value">{{ $certificate->certificate_number }}</div>
                </div>

            </div>
        </div>


        <!-- SIGNATURES -->
        <div class="signatures">
            <div class="signature-line">
                <div class="signature-label">Geschäftsführer</div>
            </div>

            <div class="signature-line">
                <div class="signature-label">Prüfungszentrum</div>
            </div>
        </div>

    </div>
</body>

</html>
