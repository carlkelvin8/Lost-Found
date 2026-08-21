@extends('layouts.app')

@section('title', 'Terms of Service - NAAP Lost & Found')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h1 class="fw-bold" style="color: #0041C7;">Terms of Service</h1>
                <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">1. Acceptance of Terms</h5>
                    <p>By accessing and using the NAAP Lost and Found System (the System), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the System. These terms apply to all users, including students, staff, and visitors of the National Aviation Academy of the Philippines (NAAP).</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">2. User Accounts</h5>
                    <p>To use certain features of the System, you may be required to create an account. You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your login credentials.</li>
                        <li>All activities that occur under your account.</li>
                        <li>Notifying NAAP immediately of any unauthorized use of your account.</li>
                    </ul>
                    <p>NAAP reserves the right to suspend or terminate accounts that violate these terms.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">3. Lost and Found Reports</h5>
                    <p>When submitting a lost or found item report, you agree to:</p>
                    <ul>
                        <li>Provide accurate and truthful information about the item.</li>
                        <li>Upload clear and relevant images when possible.</li>
                        <li>Not submit false, misleading, or fraudulent reports.</li>
                        <li>Understand that submitted reports are reviewed by NAAP staff before publication.</li>
                    </ul>
                    <p>NAAP is not responsible for any loss, damage, or theft of items reported through the System.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">4. Claims</h5>
                    <p>By submitting a claim for a found item, you represent that you are the rightful owner or authorized representative. False claims may result in:</p>
                    <ul>
                        <li>Immediate denial of the claim.</li>
                        <li>Suspension of your account.</li>
                        <li>Referral to appropriate authorities if fraud is suspected.</li>
                    </ul>
                    <p>Items must be claimed in person with valid identification at designated NAAP offices.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">5. AI-Powered Matching</h5>
                    <p>The System utilizes artificial intelligence to match lost item reports with found item reports. You acknowledge and agree that:</p>
                    <ul>
                        <li>AI matching is provided as an assistive tool and is not guaranteed to be 100% accurate.</li>
                        <li>Final verification of item ownership is conducted by NAAP staff.</li>
                        <li>AI-generated match suggestions do not constitute confirmation of ownership.</li>
                        <li>The System may store and analyze uploaded images for the purpose of item matching.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">6. Prohibited Conduct</h5>
                    <p>You agree not to:</p>
                    <ul>
                        <li>Use the System for any unlawful purpose.</li>
                        <li>Submit false or fraudulent lost and found reports.</li>
                        <li>Attempt to claim items that do not belong to you.</li>
                        <li>Interfere with or disrupt the System or its infrastructure.</li>
                        <li>Attempt to gain unauthorized access to any part of the System.</li>
                        <li>Use automated tools or bots to interact with the System.</li>
                        <li>Upload content that is offensive, harmful, or violates the rights of others.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">7. Limitation of Liability</h5>
                    <p>To the fullest extent permitted by law, NAAP shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of your use of the System. This includes but is not limited to:</p>
                    <ul>
                        <li>Loss or damage to reported items.</li>
                        <li>Errors or inaccuracies in AI matching results.</li>
                        <li>Unauthorized access to your account or data.</li>
                        <li>Any interruption or unavailability of the System.</li>
                    </ul>
                    <p>The System is provided on an as-is basis without warranties of any kind, either express or implied.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #0041C7;">8. Contact Information</h5>
                    <p>If you have any questions about these Terms of Service, please contact us:</p>
                    <ul>
                        <li><strong>NAAP Lost and Found Office</strong></li>
                        <li>Email: lostfound@naap.edu.ph</li>
                        <li>Phone: (02) 8888-NAAP</li>
                        <li>Address: National Aviation Academy of the Philippines</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
