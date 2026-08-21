@extends('layouts.app')

@section('title', 'Privacy Policy - NAAP Lost & Found')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h1 class="fw-bold" style="color: #0041C7;">Privacy Policy</h1>
                <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">Information We Collect</h5>
                    <p>When you use the NAAP Lost and Found System, we may collect the following types of information:</p>
                    <ul>
                        <li><strong>Personal Information:</strong> Name, email address, student or employee ID, and contact details when you create an account or submit a report.</li>
                        <li><strong>Report Data:</strong> Descriptions of lost or found items, including images, locations, dates, and other relevant details.</li>
                        <li><strong>Usage Data:</strong> Information about how you interact with the System, including IP address, browser type, pages visited, and time spent.</li>
                        <li><strong>Device Information:</strong> Technical data about the device and browser you use to access the System.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">How We Use Information</h5>
                    <p>We use the collected information for the following purposes:</p>
                    <ul>
                        <li>To operate and maintain the Lost and Found System.</li>
                        <li>To process lost and found reports and facilitate item recovery.</li>
                        <li>To verify user identity and manage account access.</li>
                        <li>To communicate with you regarding your reports and claims.</li>
                        <li>To improve the System and enhance user experience.</li>
                        <li>To ensure security and prevent fraudulent activity.</li>
                        <li>To comply with legal obligations and institutional policies.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">AI Image Analysis</h5>
                    <p>The System uses artificial intelligence to analyze uploaded images for the purpose of matching lost and found items. Regarding AI image analysis:</p>
                    <ul>
                        <li>Images are processed to extract visual features that assist in identifying and matching items.</li>
                        <li>AI analysis is performed solely for the purpose of facilitating item recovery.</li>
                        <li>Images are not used for facial recognition or any purpose unrelated to item matching.</li>
                        <li>AI-processed data is stored securely and is only accessible to authorized NAAP personnel.</li>
                        <li>You retain ownership of all images you upload to the System.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">Data Sharing</h5>
                    <p>We do not sell or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
                    <ul>
                        <li>With NAAP staff and administrators who need access to manage the System.</li>
                        <li>When required by law, regulation, or legal process.</li>
                        <li>To protect the rights, property, or safety of NAAP, its users, or the public.</li>
                        <li>With service providers who assist in operating the System, subject to strict data protection agreements.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">Data Security</h5>
                    <p>We implement appropriate technical and organizational measures to protect your data, including:</p>
                    <ul>
                        <li>Encryption of data in transit and at rest.</li>
                        <li>Access controls and authentication mechanisms.</li>
                        <li>Regular security assessments and monitoring.</li>
                        <li>Secure backup and disaster recovery procedures.</li>
                    </ul>
                    <p>While we strive to protect your information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">Data Retention</h5>
                    <p>We retain your information for as long as necessary to fulfill the purposes outlined in this policy:</p>
                    <ul>
                        <li>Account information is retained for the duration of your active account.</li>
                        <li>Lost and found reports are retained for a minimum of twelve (12) months from the date of submission.</li>
                        <li>Resolved claims and associated data may be retained for archival and audit purposes.</li>
                        <li>Usage data is retained for a period of six (6) months for analytics and security purposes.</li>
                    </ul>
                    <p>Upon account deletion, personal data is permanently removed or anonymized within thirty (30) days, except where retention is required by law.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">Your Rights</h5>
                    <p>You have the following rights regarding your personal data:</p>
                    <ul>
                        <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
                        <li><strong>Correction:</strong> Request correction of inaccurate or incomplete data.</li>
                        <li><strong>Deletion:</strong> Request deletion of your personal data, subject to legal retention requirements.</li>
                        <li><strong>Portability:</strong> Request a copy of your data in a structured, machine-readable format.</li>
                        <li><strong>Objection:</strong> Object to the processing of your data for certain purposes.</li>
                    </ul>
                    <p>To exercise any of these rights, please contact us using the information provided below.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">Contact Information</h5>
                    <p>If you have any questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
                    <ul>
                        <li><strong>NAAP Data Protection Officer</strong></li>
                        <li>Email: privacy@naap.edu.ph</li>
                        <li>Phone: (02) 8888-NAAP</li>
                        <li>Address: National Aviation Academy of the Philippines</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
