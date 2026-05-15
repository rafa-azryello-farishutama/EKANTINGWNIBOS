function tampilkanMode(id, nama, harga, stok, produk, foto) {
            document.getElementById('edit_harga').classList.remove('border-red-400');
            document.getElementById('edit_harga').classList.add('border-gray-200');
            document.getElementById('edit_stok').classList.remove('border-red-400');
            document.getElementById('edit_stok').classList.add('border-gray-200');
            document.getElementById('harga-menu').classList.add('hidden');
            document.getElementById('pesan-stok').classList.add('hidden');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_stok').value = stok;
            document.getElementById('edit_foto_lama').value = foto;

            const previewImg = document.getElementById('preview-img-edit');
            const placeholder = document.getElementById('placeholder-edit');

            if (foto && foto !== '') {
                previewImg.src = '../assets/img_produk/' + foto;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                previewImg.src = '';
                previewImg.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }

            // Set radio tipe
            if (produk == 'makanan') {
                document.getElementById('makanan_radio').checked = true;
            } else {
                document.getElementById('minuman_radio').checked = true;
            }

            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function tutupModalEdit() {
            document.getElementById('modal-edit').classList.add('hidden');

            document.getElementById('edit_id').value = '';
            document.getElementById('edit_nama').value = '';
            document.getElementById('edit_harga').value = '';
            document.getElementById('edit_stok').value = '';
            document.getElementById('edit_foto_lama').value = '';
            document.getElementById('input-foto-edit').value = '';

            const previewImg = document.getElementById('preview-img-edit');
            const placeholder = document.getElementById('placeholder-edit');
            previewImg.src = '';
            previewImg.classList.add('hidden');
            placeholder.classList.remove('hidden');

            document.getElementById('makanan_radio').checked = false;
            document.getElementById('minuman_radio').checked = false;

            document.getElementById('harga-menu').classList.add('hidden');
            document.getElementById('pesan-stok').classList.add('hidden');
            document.getElementById('edit_harga').classList.remove('border-red-400');
            document.getElementById('edit_harga').classList.add('border-gray-200');
            document.getElementById('edit_stok').classList.remove('border-red-400');
            document.getElementById('edit_stok').classList.add('border-gray-200');

            const form = document.querySelector('#modal-edit form');
            const errorBox = form.querySelector('.bg-red-50');
            if (errorBox) {
                errorBox.remove();
            }

            const url = new URL(window.location);
            if (url.searchParams.has('error')) {
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            }
        }

        function previewFotoEdit(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewImg = document.getElementById('preview-img-edit');
                    const placeholder = document.getElementById('placeholder-edit');
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function searchEdit() {
            document.getElementById('search-edit').classList.remove('hidden');
        }

        function tutupSearch() {
            document.getElementById('search-edit').classList.add('hidden');

            // Reset preview foto tambah
            document.getElementById('preview-img').classList.add('hidden');
            document.getElementById('preview-img').src = '';
            document.getElementById('placeholder').classList.remove('hidden');
            document.getElementById('input-foto').value = '';

            // Sembunyikan pesan error
            const pesanError = document.getElementById('pesan-error');
            if (pesanError) {
                pesanError.classList.add('hidden');
            }

            // Hapus ?error dari URL
            const url = new URL(window.location);
            if (url.searchParams.has('error')) {
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            }
        }

        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('preview-img').classList.remove('hidden');
                    document.getElementById('placeholder').classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function cekStok(input) {
            const pesan = input.nextElementSibling;
            if (parseInt(input.value) > 999) {
                input.value = 999;
                if (pesan) pesan.classList.remove('hidden');
                input.classList.add('border-red-400');
                input.classList.remove('border-gray-200');
            } else {
                if (pesan) pesan.classList.add('hidden');
                input.classList.remove('border-red-400');
                input.classList.add('border-gray-200');
            }
        }

        function cekHarga(input) {
            const pesan = input.nextElementSibling;
            if (parseInt(input.value) > 999999999) {
                input.value = 999999999;
                if (pesan) pesan.classList.remove('hidden');
                input.classList.add('border-red-400');
                input.classList.remove('border-gray-200');
            } else {
                if (pesan) pesan.classList.add('hidden');
                input.classList.remove('border-red-400');
                input.classList.add('border-gray-200');
            }
        }