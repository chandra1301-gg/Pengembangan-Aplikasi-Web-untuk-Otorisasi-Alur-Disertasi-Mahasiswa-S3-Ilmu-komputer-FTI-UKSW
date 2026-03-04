<!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5" style="margin-left: var(--sidebar-width);">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">NEWSLETTER</h5>
                    <p class="small">Keep up-to-date on our always evolving product features and technology. Enter your e-mail address and subscribe to our newsletter.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Enter your e-mail">
                        <button type="submit" class="btn btn-light">GO!</button>
                    </form>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">CONTACT US</h5>
                    <p class="small mb-2">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        Jl. O. Notohamidjojo No. 1-15, Blotongan, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50710
                    </p>
                    <p class="small mb-2">
                        <i class="bi bi-envelope-fill me-2"></i>
                        info@uksw.edu / s3tkm@uksw.edu
                    </p>
                    <p class="small mb-2">
                        <i class="bi bi-telephone-fill me-2"></i>
                        0298-321212
                    </p>
                    <p class="small mb-0">
                        <i class="bi bi-whatsapp me-2"></i>
                        089646027727 (Andi Setyowati)
                    </p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">ABOUT UKSW</h5>
                    <p class="small">Universitas Kristen Satya Wacana adalah universitas swasta yang berlokasi di Salatiga, Jawa Tengah. UKSW berkomitmen menghasilkan lulusan berkualitas dengan motto "We are the Creative Minority".</p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 small">&copy; 2025 UKSW. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small">
                        <button class="btn btn-sm text-white" style="font-size: 0.9rem;">IND</button>
                        <button class="btn btn-sm text-white" style="font-size: 0.9rem;">ENG</button>
                    </p>
                </div>
            </div>
        </div>
        <!-- Modal Preview Dokumen -->
        <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="previewModalLabel">
                            <i class="bi bi-eye me-2"></i>Preview Dokumen
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <h6 id="previewFileName" class="text-muted"></h6>
                        </div>
                        <div id="previewContent" class="text-center">
                            <!-- Konten preview akan dimuat di sini -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Tutup
                        </button>
                        <a href="#" id="downloadFromPreview" class="btn btn-primary">
                            <i class="bi bi-download me-2"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            
            // Add mobile menu button if needed
            if (window.innerWidth <= 768) {
                const mainContent = document.querySelector('.main-content');
                const menuBtn = document.createElement('button');
                menuBtn.className = 'btn btn-primary position-fixed';
                menuBtn.style.cssText = 'top: 90px; left: 10px; z-index: 1031;';
                menuBtn.innerHTML = '<i class="bi bi-list"></i>';
                menuBtn.onclick = function() {
                    sidebar.classList.toggle('show');
                };
                mainContent.prepend(menuBtn);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle preview dokumen
            const previewButtons = document.querySelectorAll('.preview-doc');
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            const previewContent = document.getElementById('previewContent');
            const previewFileName = document.getElementById('previewFileName');
            const downloadFromPreview = document.getElementById('downloadFromPreview');
            
            previewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filePath = this.getAttribute('data-file');
                    const fileName = this.getAttribute('data-filename');
                    const fileExtension = this.getAttribute('data-extension');
                    
                    previewFileName.textContent = fileName;
                    downloadFromPreview.href = filePath;
                    
                    // Tampilkan loading
                    previewContent.innerHTML = `
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat dokumen...</p>
                    `;
                    
                    // Load content berdasarkan tipe file
                    if (fileExtension === 'pdf') {
                        loadPDFPreview(filePath);
                    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                        loadImagePreview(filePath);
                    }
                });
            });
            
            function loadPDFPreview(filePath) {
                previewContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        PDF Preview akan ditampilkan di sini. Pastikan browser Anda mendukung embed PDF.
                    </div>
                    <embed src="${filePath}#toolbar=0&navpanes=0" type="application/pdf" width="100%" height="600px" />
                    <div class="mt-2">
                        <small class="text-muted">
                            Jika PDF tidak tampil, <a href="${filePath}" target="_blank" class="alert-link">klik di sini untuk membuka di tab baru</a>
                        </small>
                    </div>
                `;
            }
            
            function loadImagePreview(filePath) {
                previewContent.innerHTML = `
                    <img src="${filePath}" class="img-fluid" alt="Preview" style="max-height: 70vh; object-fit: contain;">
                    <div class="mt-2">
                        <small class="text-muted">Gambar dapat di-zoom dengan scroll mouse</small>
                    </div>
                `;
            }
            
            // Reset modal ketika ditutup
            document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
                previewContent.innerHTML = '';
                previewFileName.textContent = '';
            });
        });
    </script>
</body>
</html>