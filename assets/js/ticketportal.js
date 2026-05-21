(function () {
  var form = document.getElementById('psTicketForm');
  if (!form) return;

  var submitBtn = document.getElementById('psSubmitBtn');
  var status = document.getElementById('psStatus');
  var fileInput = document.getElementById('ps-file');
  var fileList = document.getElementById('psFileList');
  var uploadZone = document.getElementById('psUploadZone');
  var maxFiles = parseInt(form.dataset.maxFiles, 10) || 5;
  var maxSize = (parseInt(form.dataset.maxSize, 10) || 10) * 1024 * 1024;
  var selectedFiles = [];

  ['dragenter', 'dragover'].forEach(function (e) {
    uploadZone.addEventListener(e, function (ev) {
      ev.preventDefault();
      uploadZone.classList.add('ps-dragover');
    });
  });
  ['dragleave', 'drop'].forEach(function (e) {
    uploadZone.addEventListener(e, function (ev) {
      ev.preventDefault();
      uploadZone.classList.remove('ps-dragover');
    });
  });
  uploadZone.addEventListener('drop', function (ev) {
    if (ev.dataTransfer.files.length) addFiles(ev.dataTransfer.files);
  });

  fileInput.addEventListener('change', function () {
    if (this.files.length) addFiles(this.files);
    this.value = '';
  });

  function addFiles(files) {
    for (var i = 0; i < files.length; i++) {
      if (files[i].size > maxSize) {
        alert('Datei "' + files[i].name + '" ist größer als ' + (maxSize / 1024 / 1024) + ' MB.');
        continue;
      }
      if (selectedFiles.length >= maxFiles) {
        alert('Maximal ' + maxFiles + ' Dateien erlaubt.');
        break;
      }
      selectedFiles.push(files[i]);
    }
    renderFiles();
  }

  function renderFiles() {
    fileList.innerHTML = '';
    selectedFiles.forEach(function (f, i) {
      var item = document.createElement('div');
      item.className = 'ps-ticket__file-item';
      var sizeMB = (f.size / 1024 / 1024).toFixed(1);
      item.innerHTML =
        '<span class="ps-ticket__file-name">' + escapeHtml(f.name) + ' (' + sizeMB + ' MB)</span>' +
        '<button type="button" class="ps-ticket__file-remove" data-index="' + i + '">&times;</button>';
      fileList.appendChild(item);
    });
    fileList.querySelectorAll('.ps-ticket__file-remove').forEach(function (btn) {
      btn.addEventListener('click', function () {
        selectedFiles.splice(parseInt(this.dataset.index, 10), 1);
        renderFiles();
      });
    });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function validate() {
    var valid = true;
    form.querySelectorAll('[required]').forEach(function (el) {
      var field = el.closest('.ps-ticket__field');
      var errSpan = field ? field.querySelector('.ps-ticket__error-msg') : null;
      el.classList.remove('ps-error');
      if (errSpan) errSpan.textContent = '';

      if (el.type === 'checkbox') {
        if (!el.checked) {
          valid = false;
          el.style.borderColor = '#ed5e5e';
        } else {
          el.style.borderColor = '';
        }
        return;
      }

      if (!el.value.trim()) {
        valid = false;
        el.classList.add('ps-error');
        if (errSpan) errSpan.textContent = 'Pflichtfeld';
      } else if (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
        valid = false;
        el.classList.add('ps-error');
        if (errSpan) errSpan.textContent = 'Ungültige E-Mail-Adresse';
      }
    });
    return valid;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    status.className = 'ps-ticket__status';
    status.style.display = 'none';

    if (!validate()) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="ps-ticket__spinner"></span> Wird gesendet...';

    var fd = new FormData();
    fd.append('action', 'ps_ticket_submit');
    fd.append('nonce', psTicketVars.nonce);
    fd.append('name', form.name.value.trim());
    fd.append('email', form.email.value.trim());
    fd.append('phone', form.phone.value.trim());
    fd.append('company', form.company.value.trim());
    fd.append('subject', form.subject.value.trim());
    fd.append('category', form.category.value);
    fd.append('message', form.message.value.trim());

    selectedFiles.forEach(function (f) {
      fd.append('files[]', f);
    });

    fetch(psTicketVars.ajaxUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          status.className = 'ps-ticket__status ps-ticket__status--success';
          status.textContent = 'Ticket erfolgreich erstellt! Wir melden uns in Kürze bei Ihnen.';
          status.style.display = 'block';
          form.reset();
          selectedFiles = [];
          renderFiles();
        } else {
          throw new Error(data.data || 'Unbekannter Fehler');
        }
      })
      .catch(function (err) {
        status.className = 'ps-ticket__status ps-ticket__status--error';
        status.textContent = 'Fehler: ' + err.message;
        status.style.display = 'block';
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.innerHTML =
          '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg> Ticket absenden';
      });
  });

  form.querySelectorAll('.ps-ticket__input, .ps-ticket__select, .ps-ticket__textarea').forEach(function (el) {
    el.addEventListener('input', function () {
      this.classList.remove('ps-error');
      var field = this.closest('.ps-ticket__field');
      var errSpan = field ? field.querySelector('.ps-ticket__error-msg') : null;
      if (errSpan) errSpan.textContent = '';
    });
  });
})();
