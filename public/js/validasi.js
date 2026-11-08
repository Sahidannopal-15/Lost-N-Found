/**
 * public/js/validasi.js
 * Validasi sisi klien (jQuery) untuk form register, login, laporan, dan klaim.
 *
 * Aturan dan pesan berbasiskan PRD §10/§11. Layar validasi server tetap berjalan.
 * Dipakai semua form lewat @push('skrip') di masing-masing view.
 */
(function ($) {
    'use strict';

    function showError($field, message) {
        $field.addClass('is-invalid');
        var $feedback = $field.closest('.mb-3').find('.invalid-feedback');
        if (!$feedback.length) {
            $feedback = $('<div class="invalid-feedback"></div>').insertAfter($field);
        }
        $feedback.text(message).show();
    }

    function clearError($field) {
        $field.removeClass('is-invalid');
        var $feedback = $field.closest('.mb-3').find('.invalid-feedback');
        if ($feedback.length) {
            $feedback.empty().hide();
        }
    }

    function validateRegisters($form) {
        var $name = $form.find('#name');
        var $email = $form.find('#email');
        var $password = $form.find('#password');
        var $confirm = $form.find('#password_confirmation');
        var ok = true;

        if ($name.length && $.trim($name.val()) === '') {
            showError($name, 'Nama belum diisi.');
            ok = false;
        } else {
            clearError($name);
        }

        if ($email.length && $.trim($email.val()) === '') {
            showError($email, 'Email belum diisi.');
            ok = false;
        } else if ($email.length) {
            clearError($email);
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRe.test($email.val())) {
                showError($email, 'Format email tidak valid, contoh: nama@email.com.');
                ok = false;
            }
        }

        if ($password.length && $password.val().length < 8) {
            showError($password, 'Password minimal 8 karakter.');
            ok = false;
        } else if ($password.length) {
            clearError($password);
        }

        if ($confirm.length && $confirm.val() !== $password.val()) {
            showError($confirm, 'Konfirmasi password tidak sama.');
            ok = false;
        } else if ($confirm.length) {
            clearError($confirm);
        }

        return ok;
    }

    function validateLogins($form) {
        var $email = $form.find('#email');
        var $password = $form.find('#password');
        var ok = true;

        if ($email.length && $password.length && ($.trim($email.val()) === '' || $.trim($password.val()) === '')) {
            if ($email.length) {
                showError($email, 'Email atau password belum diisi.');
            }
            if ($password.length) {
                showError($password, 'Email atau password belum diisi.');
            }
            ok = false;
        } else {
            if ($email.length) {
                clearError($email);
            }
            if ($password.length) {
                clearError($password);
            }
        }

        return ok;
    }

    function validateItems($form) {
        var $type = $form.find('#type');
        var $title = $form.find('#title');
        var $description = $form.find('#description');
        var $category = $form.find('#category_id');
        var $location = $form.find('#location_id');
        var $date = $form.find('#event_date');
        var $photo = $form.find('#photo');
        var ok = true;

        if ($type.length && $type.val() === '') {
            showError($type, 'Jenis laporan belum dipilih.');
            ok = false;
        } else if ($type.length) {
            clearError($type);
        }

        if ($title.length && $.trim($title.val()) === '') {
            showError($title, 'Judul barang belum diisi.');
            ok = false;
        } else if ($title.length) {
            clearError($title);
        }

        if ($description.length && $.trim($description.val()) === '') {
            showError($description, 'Deskripsi belum diisi.');
            ok = false;
        } else if ($description.length) {
            clearError($description);
        }

        if ($category.length && $category.val() === '') {
            showError($category, 'Kategori belum dipilih.');
            ok = false;
        } else if ($category.length) {
            clearError($category);
        }

        if ($location.length && $location.val() === '') {
            showError($location, 'Lokasi belum dipilih.');
            ok = false;
        } else if ($location.length) {
            clearError($location);
        }

        if ($date.length && ($date.val() === '' || new Date($date.val()) > new Date(new Date().setHours(0, 0, 0, 0)))) {
            showError($date, 'Tanggal tidak boleh lebih dari hari ini.');
            ok = false;
        } else if ($date.length) {
            clearError($date);
        }

        if ($photo.length && $photo.prop('files') && $photo.prop('files').length > 0) {
            var file = $photo.prop('files')[0];
            var validType = file.type === 'image/jpeg' || file.type === 'image/png' || file.name.toLowerCase().match(/\.(jpg|jpeg|png)$/i);
            if (!validType || file.size > 2 * 1024 * 1024) {
                showError($photo, 'Foto harus berformat JPG atau PNG, maksimal 2 MB.');
                ok = false;
            } else {
                clearError($photo);
            }
        }

        return ok;
    }

    function validateClaims($form) {
        var $description = $form.find('#description');
        var $proof = $form.find('#proof');
        var ok = true;

        if ($description.length && $.trim($description.val()).length < 20) {
            showError($description, 'Ciri barang minimal 20 karakter. Sebutkan hal yang hanya diketahui pemilik, misalnya isi tas atau goresan.');
            ok = false;
        } else if ($description.length) {
            clearError($description);
        }

        if ($proof.length && $.trim($proof.val()).length < 10) {
            showError($proof, 'Bukti minimal 10 karakter.');
            ok = false;
        } else if ($proof.length) {
            clearError($proof);
        }

        return ok;
    }

    function attachValidations() {
        var $register = $('#form-register');
        var $login = $('#form-login');
        var $items = $('#form-items');
        var $claims = $('#form-claims');

        $register.attr('novalidate', 'novalidate');
        $login.attr('novalidate', 'novalidate');
        $items.attr('novalidate', 'novalidate');
        $claims.attr('novalidate', 'novalidate');

        $register.on('submit', function (e) {
            e.preventDefault();
            if (!validateRegisters($register)) {
                var $first = $register.find('.is-invalid').first();
                if ($first.length) {
                    $first.focus();
                }
                return;
            }
            $register[0].submit();
        });

        $login.on('submit', function (e) {
            e.preventDefault();
            if (!validateLogins($login)) {
                var $first = $login.find('.is-invalid').first();
                if ($first.length) {
                    $first.focus();
                }
                return;
            }
            $login[0].submit();
        });

        $items.on('submit', function (e) {
            e.preventDefault();
            if (!validateItems($items)) {
                var $first = $items.find('.is-invalid').first();
                if ($first.length) {
                    $first.focus();
                }
                return;
            }
            $items[0].submit();
        });

        $claims.on('submit', function (e) {
            e.preventDefault();
            if (!validateClaims($claims)) {
                var $first = $claims.find('.is-invalid').first();
                if ($first.length) {
                    $first.focus();
                }
                return;
            }
            $claims[0].submit();
        });
    }

    $(function () {
        attachValidations();
        window.__validasiLoaded = true;
    });
})(jQuery);
