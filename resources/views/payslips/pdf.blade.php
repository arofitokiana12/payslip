<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Paie - {{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10px;
            color: #666;
            line-height: 1.6;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .period {
            font-size: 14px;
            color: #667eea;
            font-weight: bold;
        }

        /* Employee & Employer Info */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .info-box {
            display: table-cell;
            width: 48%;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-box + .info-box {
            margin-left: 4%;
        }

        .info-box h3 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }

        .info-line {
            margin-bottom: 6px;
            display: table;
            width: 100%;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 40%;
            color: #555;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            color: #333;
        }

        /* Salary Details Table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .salary-table th {
            background: #667eea;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .salary-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .salary-table tr:last-child td {
            border-bottom: none;
        }

        .salary-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .earnings-section {
            background: #f0fdf4;
        }

        .deductions-section {
            background: #fef2f2;
        }

        .section-header {
            background: #f8f9fa !important;
            font-weight: bold;
            color: #333 !important;
            font-size: 12px;
        }

        /* Summary Box */
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 25px;
            text-align: center;
        }

        .summary-box h2 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .net-salary {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 50px;
            margin-bottom: 20px;
        }

        .signature-box {
            display: table-cell;
            width: 48%;
            text-align: center;
        }

        .signature-box + .signature-box {
            margin-left: 4%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 8px;
            font-weight: bold;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-finalized {
            background: #d1fae5;
            color: #065f46;
        }

        .status-paid {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Watermark for draft */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
    @if($payslip->status === 'draft')
        <div class="watermark">BROUILLON</div>
    @endif

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company->company_name ?? 'PAYFLEX SARL' }}</div>
                <div class="company-info">
                    {{ $company->adress ?? 'Adresse de l\'entreprise' }}<br>
                    NIF: {{ $company->nif ?? 'N/A' }} | STAT: {{ $company->stat ?? 'N/A' }}<br>
                    Email: {{ $company->email ?? 'contact@payflex.mg' }}
                </div>
            </div>
            <div class="header-right">
                <div class="document-title">BULLETIN DE PAIE</div>
                <div class="period">
                    {{ $monthName }} {{ $payslip->period_year }}
                </div>
                <span class="status-badge status-{{ $payslip->status }}">
                    {{ strtoupper($statusLabel) }}
                </span>
            </div>
        </div>

        <!-- Employee & Employer Info -->
        <div class="info-section">
            <div class="info-box">
                <h3>👤 INFORMATIONS EMPLOYEUR</h3>
                <div class="info-line">
                    <span class="info-label">Raison sociale:</span>
                    <span class="info-value">{{ $company->company_name ?? 'N/A' }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">Adresse:</span>
                    <span class="info-value">{{ $company->adress ?? 'N/A' }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">NIF:</span>
                    <span class="info-value">{{ $company->nif ?? 'N/A' }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">STAT:</span>
                    <span class="info-value">{{ $company->stat ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="info-box">
                <h3>👨‍💼 INFORMATIONS SALARIÉ</h3>
                <div class="info-line">
                    <span class="info-label">Nom complet:</span>
                    <span class="info-value">
                        {{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }}
                    </span>
                </div>
                <div class="info-line">
                    <span class="info-label">Matricule:</span>
                    <span class="info-value">{{ $payslip->employee->matricule }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">Poste:</span>
                    <span class="info-value">{{ $payslip->employee->position->position_name ?? 'N/A' }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">Date d'embauche:</span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($payslip->employee->hire_date)->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Salary Details -->
        <table class="salary-table">
            <thead>
                <tr>
                    <th style="width: 70%">LIBELLÉ</th>
                    <th style="width: 30%; text-align: right;">MONTANT (MGA)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Earnings Section -->
                <tr class="section-header">
                    <td colspan="2">💰 GAINS ET RÉMUNÉRATIONS</td>
                </tr>
                @foreach($payslip->items->where('item_type', 'earning') as $item)
                <tr class="earnings-section">
                    <td>{{ $item->item_name }}</td>
                    <td class="amount">{{ number_format($item->amount, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
                <tr style="background: #e8f5e9; font-weight: bold;">
                    <td>TOTAL BRUT</td>
                    <td class="amount">{{ number_format($payslip->total_earnings, 2, ',', ' ') }}</td>
                </tr>

                <!-- Deductions Section -->
                <tr class="section-header">
                    <td colspan="2">📉 RETENUES ET COTISATIONS</td>
                </tr>
                @foreach($payslip->items->whereIn('item_type', ['deduction', 'tax']) as $item)
                <tr class="deductions-section">
                    <td>{{ $item->item_name }}</td>
                    <td class="amount" style="color: #dc2626;">
                        -{{ number_format($item->amount, 2, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
                <tr style="background: #fee2e2; font-weight: bold;">
                    <td>TOTAL RETENUES</td>
                    <td class="amount" style="color: #dc2626;">
                        -{{ number_format($payslip->total_deductions, 2, ',', ' ') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Net Salary Summary -->
        <div class="summary-box">
            <h2>SALAIRE NET À PAYER</h2>
            <div class="net-salary">{{ number_format($payslip->net_salary, 2, ',', ' ') }} MGA</div>
            <p style="font-size: 10px; margin-top: 10px; opacity: 0.9;">
                En lettres: {{ $netSalaryInWords }}
            </p>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div>L'Employeur</div>
                <div class="signature-line">Signature et cachet</div>
            </div>
            <div class="signature-box">
                <div>Le Salarié</div>
                <div class="signature-line">Signature</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Document généré le {{ now()->format('d/m/Y à H:i') }}</strong><br>
                Ce bulletin de paie est conforme à la législation malgache en vigueur.<br>
                Pour toute réclamation, veuillez contacter le service RH dans un délai de 30 jours.
            </p>
        </div>
    </div>
</body>
</html>
