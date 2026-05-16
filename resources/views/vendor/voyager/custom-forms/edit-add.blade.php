@extends('voyager::bread.edit-add')

@section('css')
    @parent
    <style>
        .integration-panel {
            background: #f9f9f9;
            border: 1px solid #e1e1e1;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .integration-panel h4 {
            margin-top: 0;
            color: #22A7F0;
        }
        /* Ocultar campos del sistema */
        .form-group:has(input[name="uuid"]),
        .form-group:has(textarea[name="description"]) {
            display: none !important;
        }
        .copy-box {
            background: #eee;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            position: relative;
            margin-bottom: 10px;
        }
        .copy-btn {
            position: absolute;
            right: 5px;
            top: 5px;
        }
        .field-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .field-item input {
            flex: 1;
            margin-right: 10px;
        }
    </style>
@stop

@section('content')
    @php
        $edit = !is_null($dataTypeContent->getKey());
        $add  = is_null($dataTypeContent->getKey());
    @endphp
    <div class="page-content edit-add container-fluid">
        @if($edit)
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered integration-panel">
                    <div class="panel-body">
                        <h4><i class="voyager-link"></i> Información de Integración para Frontend / IA</h4>
                        <p>Usa la siguiente información para conectar este formulario con tu Landing Page o Aplicación.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Endpoint URL:</strong>
                                <div class="copy-box">
                                    <span id="api-url">{{ url('/api/forms/submit') }}</span>
                                    <button class="btn btn-xs btn-default copy-btn" onclick="copyToClipboard('#api-url')">Copiar</button>
                                </div>
                                
                                <strong>Método HTTP:</strong>
                                <div class="copy-box">POST</div>
                            </div>
                            <div class="col-md-6">
                                <strong>Form ID (UUID):</strong>
                                <div class="copy-box">
                                    <span id="form-uuid">{{ $dataTypeContent->uuid }}</span>
                                    <button class="btn btn-xs btn-default copy-btn" onclick="copyToClipboard('#form-uuid')">Copiar</button>
                                </div>
                                
                                <strong>Payload de Ejemplo:</strong>
                                <div style="position:relative; margin-bottom:10px;">
                                    <textarea id="payload-example" class="form-control" readonly style="min-height: 120px; font-family: monospace; resize:vertical; background-color:#eee;"></textarea>
                                    <button class="btn btn-xs btn-default copy-btn" onclick="copyToClipboard('#payload-example')" style="position:absolute; top: 5px; right: 5px;">Copiar</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <strong>Código Completo (Snippet) para Landing Page:</strong>
                                <p class="text-muted"><small>Copia y pega este código en tu archivo HTML. Ya incluye el diseño del formulario, el Form ID correcto y el script de conexión.</small></p>
                                <div style="position:relative; margin-bottom:10px;">
                                    <textarea id="full-snippet" class="form-control" readonly style="min-height: 250px; font-family: monospace; resize:vertical; background-color:#eee;">{{ $dataTypeContent->description }}</textarea>
                                    <button class="btn btn-xs btn-primary copy-btn" onclick="copyToClipboard('#full-snippet')" style="position:absolute; top: 5px; right: 5px;">Copiar Todo el Snippet</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @parent
    </div>
@stop

@section('javascript')
    @parent
    <script>
        function copyToClipboard(element) {
            var $el = $(element);
            var textToCopy = $el.is('textarea, input') ? $el.val() : $el.text();
            
            var $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(textToCopy).select();
            document.execCommand("copy");
            $temp.remove();
            toastr.success('Copiado al portapapeles');
        }

        $(document).ready(function() {
            // Find the JSON editor field for 'fields'
            var $fieldsTextarea = $('textarea[name="fields"]');
            var $aceEditorDiv = $('#fields');
            var $fieldsContainer = $fieldsTextarea.closest('.form-group');
            
            // Create a friendlier UI for fields
            var $uiContainer = $('<div id="fields-ui-container" style="margin-top:10px;"></div>');
            var $fieldsList = $('<div id="fields-list"></div>');
            var $addButton = $('<button type="button" class="btn btn-success btn-sm"><i class="voyager-plus"></i> Añadir Campo</button>');
            
            $uiContainer.append($fieldsList).append($addButton);
            $fieldsContainer.append($uiContainer);
            
            // Hide the raw textarea and the native Ace Editor completely
            $fieldsTextarea.hide();
            $aceEditorDiv.hide();
            $fieldsTextarea.siblings('.ace_editor').hide();
            
            // Also add a little helper text
            $uiContainer.prepend('<p class="text-muted"><small>Agrega los campos que deseas que tenga tu formulario (ej: nombre, email, telefono). El sistema generará automáticamente el JSON.</small></p>');

            // Hide system generated fields so the user doesn't get confused
            $('input[name="uuid"]').closest('.form-group').hide();
            $('textarea[name="description"]').closest('.form-group').hide();
            $('#description').closest('.form-group').hide();
            
            // ── Inject Evento & Landing Page selectors ──────────────────────────
            // Only do this if they are NOT already rendered by Voyager as relationship
            // dropdowns (Voyager renders them when data_rows exist for those fields).
            // We inject a helper note above those selects so the user knows what they are.
            var $eventSelect = $('select[name="event_id"]');
            var $landingSelect = $('select[name="landing_page_id"]');
            
            if ($eventSelect.length) {
                $eventSelect.closest('.form-group').find('label').after(
                    '<p class="text-muted" style="margin-bottom:6px;"><small>Asocia este formulario a un Evento para identificar sus respuestas.</small></p>'
                );
            }
            if ($landingSelect.length) {
                $landingSelect.closest('.form-group').find('label').after(
                    '<p class="text-muted" style="margin-bottom:6px;"><small>Asocia este formulario a una Landing Page para identificar su origen.</small></p>'
                );
            }
            // ────────────────────────────────────────────────────────────────────

            function updateJsonFromUi() {
                var fields = [];
                $('.field-input').each(function() {
                    var val = $(this).val().trim();
                    if (val) fields.push(val);
                });
                $fieldsTextarea.val(JSON.stringify(fields));
                updatePayloadExample(fields);
            }
            
            function updatePayloadExample(fields) {
                var payload = {
                    form_id: $('#form-uuid').text() || 'YOUR-FORM-UUID'
                };
                fields.forEach(function(field) {
                    payload[field] = 'valor_de_ejemplo';
                });
                $('#payload-example').val(JSON.stringify(payload, null, 4));
            }
            
            function addFieldRow(value) {
                var $row = $('<div class="field-item"></div>');
                var $input = $('<input type="text" class="form-control field-input" placeholder="Nombre del campo (ej: email)">').val(value || '');
                var $removeBtn = $('<button type="button" class="btn btn-danger btn-sm"><i class="voyager-trash"></i></button>');
                var $duplicateBtn = $('<button type="button" class="btn btn-info btn-sm" style="margin-right:5px;"><i class="voyager-double-right"></i></button>');
                
                $row.append($input).append($duplicateBtn).append($removeBtn);
                $fieldsList.append($row);
                
                $input.on('input', updateJsonFromUi);
                $removeBtn.on('click', function() {
                    $row.remove();
                    updateJsonFromUi();
                });
                $duplicateBtn.on('click', function() {
                    addFieldRow($input.val());
                    updateJsonFromUi();
                });
            }
            
            // Initialize from existing data
            try {
                var raw = $fieldsTextarea.val() || '[]';
                var initialFields = JSON.parse(raw);
                if (Array.isArray(initialFields)) {
                    initialFields.forEach(function(field) {
                        addFieldRow(field);
                    });
                }
                updatePayloadExample(initialFields);
            } catch (e) {
                console.error("Invalid JSON in fields", e);
            }
            
            $addButton.on('click', function() {
                addFieldRow('');
            });
        });
    </script>
@stop
