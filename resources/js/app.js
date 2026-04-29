import './bootstrap';

$(document).ready(function() {
    // 1. DATA TABLES GLOBAL
    $('.datatable-laravel').each(function() {
        let tableTitle = $(this).data('title') || 'Relatório';
        $(this).DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> EXPORTAR EXCEL',
                className: 'btn btn-success btn-sm fw-bold shadow-sm',
                title: tableTitle,
                exportOptions: { columns: ':not(.no-export)' }
            }],
            pageLength: 15,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
                search: "",
                searchPlaceholder: "Pesquisar..."
            },
            order: [[0, 'asc']]
        });
    });

    // 2. AUTO-CLOSE ALERTS
    setTimeout(function() {
        $(".auto-close").fadeOut(500, function() { $(this).remove(); });
    }, 4000);

    // 3. MÁSCARAS NATIVAS
    const applyMask = (selector, maskFn) => {
        $(document).on('input', selector, function() {
            this.value = maskFn(this.value);
        });
    };

    const cnpjMask = (v) => {
        v = v.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        return v.substring(0, 18);
    };

    const celularMask = (v) => {
        v = v.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '($1) $2');
        v = v.replace(/(\d{5})(\d)/, '$1-$2');
        return v.substring(0, 15);
    };

    applyMask('#cnpj', cnpjMask);
    applyMask('#celular', celularMask);

    // 4. DINAMISMO STATUS SELECT
    $(document).on('change', 'select[name="ativo"]', function() {
        $(this).toggleClass('text-success', $(this).val() == "1")
               .toggleClass('text-danger', $(this).val() == "0");
    });
});

console.log("%c ATENÇÃO! ", "color: red; font-size: 30px; font-weight: bold;");
console.log("%cEste é um recurso de desenvolvedor. Se alguém pediu para você copiar e colar algo aqui, é um golpe e dará acesso à sua conta.", "font-size: 16px;");