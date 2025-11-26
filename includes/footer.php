            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <!-- <script src="/MCS/assets/js/main.js"></script> -->

    <!-- Footer -->
    <footer class="footer-custom mt-5 pt-3 pb-2">
        <div class="container">
            <div class="row gy-3 gx-4 align-items-start justify-content-center">
                <div class="col-12 col-md-5 text-center text-md-start mb-3 mb-md-0">
                    <div class="footer-title mb-1">Milk Cooperative System</div>
                    <div class="footer-copyright mb-1">&copy; <?php echo date('Y'); ?>. All rights reserved.</div>
                    <div class="footer-about mb-2">Empowering farmers and industries for a better tomorrow.<br>Our platform connects local farmers and industries, ensuring quality, transparency, and fair trade in the dairy sector.</div>
                </div>
                <div class="d-none d-md-block col-md-1 px-0">
                    <div class="footer-divider"></div>
                </div>
                <div class="col-12 col-md-3 text-center text-md-start mb-3 mb-md-0">
                    <div class="footer-section-title mb-1">Contact Us</div>
                    <div class="footer-contact-info mb-1">Email: <a href="mailto:info@milkcoopsystem.com" class="footer-contact">info@milkcoopsystem.com</a></div>
                    <div class="footer-contact-info mb-1">Phone: <a href="tel:+255 628 030 877" class="footer-contact">+255 628 030 877</a></div>
                    <div class="footer-contact-info">Address: Moshi Co-Operative University, Kilimanjaro - Tanzania</div>
                </div>
                <div class="d-none d-md-block col-md-1 px-0">
                    <div class="footer-divider"></div>
                </div>
                <div class="col-12 col-md-2 text-center text-md-start mb-3 mb-md-0">
                    <div class="footer-section-title mb-1">Legal</div>
                    <div><a href="#" class="footer-link-plain">Privacy Policy</a></div>
                    <div><a href="#" class="footer-link-plain">Terms of Service</a></div>
                </div>
                <div class="col-12 col-md-12 d-flex justify-content-center align-items-center mt-3">
                    <div class="footer-socials d-flex gap-3">
                        <a href="#" class="footer-social bg-facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-social bg-twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="footer-social bg-youtube" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="footer-social bg-github" title="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
    .footer-custom {
        background: linear-gradient(90deg, #6C63FF 0%, #8B7FFF 100%);
        color: #fff;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
        box-shadow: 0 -2px 32px 0 rgba(108,99,255,0.13), 0 8px 32px 0 rgba(108,99,255,0.10);
        margin-top: 3rem;
        margin-bottom: 0;
        font-size: 1.08rem;
        backdrop-filter: blur(2.5px);
        position: relative;
        padding-top: 1.5rem !important;
        padding-bottom: 0.5rem !important;
    }
    .footer-title {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 0.3rem;
    }
    .footer-copyright {
        font-size: 1rem;
        color: #e0e0e0;
        margin-bottom: 0.3rem;
    }
    .footer-about {
        color: #e0e0e0;
        font-size: 0.98rem;
        line-height: 1.5;
        margin-bottom: 0.3rem;
    }
    .footer-section-title {
        font-weight: 700;
        font-size: 1.08rem;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
    }
    .footer-contact-info {
        color: #e0e0e0;
        font-size: 0.98rem;
        margin-bottom: 0.15rem;
    }
    .footer-link-plain {
        color: #fff;
        text-decoration: underline;
        font-weight: 400;
        transition: color 0.2s;
        font-size: 0.98rem;
    }
    .footer-link-plain:hover {
        color: #e0e0e0;
        text-decoration: underline;
    }
    .footer-divider {
        width: 2px;
        height: 80%;
        min-height: 60px;
        background: rgba(255,255,255,0.18);
        border-radius: 2px;
        margin: 0 auto;
    }
    .footer-socials {
        margin-top: 0.2rem;
    }
    .footer-social {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #fff;
        font-size: 1.25rem;
        margin: 0 4px;
        transition: background 0.2s, color 0.2s, transform 0.2s;
        background: rgba(255,255,255,0.13);
        box-shadow: 0 2px 8px rgba(108,99,255,0.10);
    }
    .footer-social:hover {
        color: #fff;
        transform: scale(1.10);
        box-shadow: 0 4px 16px rgba(108,99,255,0.18);
    }
    .bg-facebook { background: #3b5998 !important; }
    .bg-twitter { background: #1da1f2 !important; }
    .bg-youtube { background: #ff0000 !important; }
    .bg-github { background: #24292e !important; }
    .footer-contact {
        color: #fff;
        text-decoration: underline;
        font-weight: 400;
    }
    .footer-contact:hover {
        color: #e0e0e0;
        text-decoration: underline;
    }
    @media (max-width: 991.98px) {
        .footer-custom {
            border-radius: 18px 18px 0 0;
            padding: 1.2rem 0.5rem 0.8rem 0.5rem !important;
        }
        .footer-section-title {
            margin-top: 1.2rem;
        }
        .footer-custom .row > div {
            text-align: center !important;
        }
        .footer-divider {
            display: none;
        }
        .footer-socials {
            margin-top: 1rem;
        }
    }
    @media (max-width: 767.98px) {
        .footer-custom .row {
            flex-direction: column !important;
        }
        .footer-custom .row > div {
            margin-bottom: 0.8rem;
        }
        .footer-socials {
            margin-top: 0.8rem;
        }
    }
    </style>
</body>
</html> 