<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-sm-4">
                <form class="card">
                    <div class="card-body">
                        <h2 class="text-center">Dymo Printer</h2>
                        <div id="alertPad"></div>
                        <div class="mb-3 mt-3">
                            <label for="sampleText" class="form-label">TRG ID:</label>
                            <input type="number" class="form-control" id="sampleText" placeholder="Input TRG ID">
                        </div>
                        <div class="my-3">
                            <label class="form-label" for="printersSelect">Printer:</label>
                            <select class="form-control" id="printersSelect"></select>
                        </div>
                        <div id="printerDetail"></div>
                        <div class="text-end my-3">
                            <button type="button" id="resetButton" class="btn btn-danger">Reset</button>
                            <button type="button" id="printButton" class="btn btn-primary">Print</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/plugins/dymo/dymo.connect.framework.js"></script>

    <script>
        (function() {
            // stores loaded label info
            var label;
            var _printers = [];

            function createPrintersTableRow(table, name, value) {
                var row = document.createElement("tr");

                var cell1 = document.createElement("td");
                cell1.appendChild(document.createTextNode(name + ': '));

                var cell2 = document.createElement("td");
                cell2.appendChild(document.createTextNode(value));

                row.appendChild(cell1);
                row.appendChild(cell2);

                table.appendChild(row);
            }

            function populatePrinterDetail() {
                var printerDetail = document.getElementById("printerDetail");
                printerDetail.innerHTML = "";

                var myPrinter = _printers[document.getElementById("printersSelect").value];
                if (myPrinter === undefined)
                    return;

                var table = document.createElement("table");
                createPrintersTableRow(table, 'PrinterType', myPrinter['printerType'])
                createPrintersTableRow(table, 'PrinterName', myPrinter['name'])
                createPrintersTableRow(table, 'ModelName', myPrinter['modelName'])
                createPrintersTableRow(table, 'IsLocal', myPrinter['isLocal'])
                createPrintersTableRow(table, 'IsConnected', myPrinter['isConnected'])
                createPrintersTableRow(table, 'IsTwinTurbo', myPrinter['isTwinTurbo'])

                dymo.label.framework.is550PrinterAsync(myPrinter.name).then(function (isRollStatusSupported) {
                    //fetch one consumable information in the printer list.
                    if (isRollStatusSupported) {
                        createPrintersTableRow(table, 'IsRollStatusSupported', 'True')
                        dymo.label.framework.getConsumableInfoIn550PrinterAsync(myPrinter.name).then(function (consumableInfo) {
                            createPrintersTableRow(table, 'SKU', consumableInfo['sku'])
                            createPrintersTableRow(table, 'Consumable Name', consumableInfo['name'])
                            createPrintersTableRow(table, 'Labels Remaining', consumableInfo['labelsRemaining'])
                            createPrintersTableRow(table, 'Roll Status', consumableInfo['rollStatus'])
                        }).thenCatch(function (e) {
                            createPrintersTableRow(table, 'SKU', 'n/a')
                            createPrintersTableRow(table, 'Consumable Name', 'n/a')
                            createPrintersTableRow(table, 'Labels Remaining', 'n/a')
                            createPrintersTableRow(table, 'Roll Status', 'n/a')
                        })
                    } else {
                        createPrintersTableRow(table, 'IsRollStatusSupported', 'False')
                    }
                }).thenCatch(function (e) {
                    createPrintersTableRow(table, 'IsRollStatusSupported', e.message)
                })

                printerDetail.appendChild(table);

                sampleText.disabled = false;
            }

            // called when the document completly loaded
            function onload() {
                var printersSelect = document.getElementById('printersSelect');
                var printButton = document.getElementById('printButton');
                var resetButton = document.getElementById('resetButton');
                var sampleText = document.getElementById('sampleText');
                const alertPlaceholder = document.getElementById('alertPad');

                // initialize controls
                printButton.disabled = true;
                sampleText.disabled = true;

                // loads all supported printers into a combo box
                function loadPrinters() {
                    _printers = [];
                    dymo.label.framework.getPrintersAsync().then(function (printers) {
                        if (printers.length == 0) {
                            alert("No DYMO printers are installed. Install DYMO printers.");
                            // const wrapper = document.createElement('div');
                            // wrapper.innerHTML = [
                            //     `<div class="alert alert-danger alert-dismissible" role="alert">`,
                            //     `   <div>No DYMO printers are installed. Install DYMO printers.</div>`,
                            //     '</div>'
                            // ].join('');
                            // alertPlaceholder.append(wrapper);
                            return;
                        }
                        _printers = printers;
                        printers.forEach(function (printer) {
                            let printerName = printer["name"];
                            let option = document.createElement("option");
                            option.value = printerName;
                            option.appendChild(document.createTextNode(printerName));
                            printersSelect.appendChild(option);
                        });
                        populatePrinterDetail();
                    }).thenCatch(function (e) {
                        alert("Load Printers failed: " + e);;
                        return;
                    });
                }

                sampleText.onchange = function() {
                    var text = sampleText.value;
                    var testAddressLabelXml = '<?xml version="1.0" encoding="utf-8"?>\
    <DieCutLabel Version="8.0" Units="twips">\
        <PaperOrientation>Landscape</PaperOrientation>\
        <Id>Address</Id>\
        <PaperName>30252 Address</PaperName>\
        <DrawCommands>\
            <RoundRectangle X="0" Y="0" Width="1581" Height="5040" Rx="270" Ry="270" />\
        </DrawCommands>\
        <ObjectInfo>\
            <AddressObject>\
                <Name>Address</Name>\
                <ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
                <BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
                <LinkedObjectName></LinkedObjectName>\
                <Rotation>Rotation0</Rotation>\
                <IsMirrored>False</IsMirrored>\
                <IsVariable>True</IsVariable>\
                <HorizontalAlignment>Left</HorizontalAlignment>\
                <VerticalAlignment>Middle</VerticalAlignment>\
                <TextFitMode>ShrinkToFit</TextFitMode>\
                <UseFullFontHeight>True</UseFullFontHeight>\
                <Verticalized>False</Verticalized>\
                <StyledText>\
                    <Element>\
                        <String>DYMO\n3 Glenlake Parkway\nAtlanta, GA 30328</String>\
                        <Attributes>\
                            <Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
                            <ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
                        </Attributes>\
                    </Element>\
                </StyledText>\
                <ShowBarcodeFor9DigitZipOnly>False</ShowBarcodeFor9DigitZipOnly>\
                <BarcodePosition>AboveAddress</BarcodePosition>\
                <LineFonts>\
                    <Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
                    <Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
                    <Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
                </LineFonts>\
            </AddressObject>\
            <Bounds X="332" Y="150" Width="4455" Height="1260" />\
        </ObjectInfo>\
    </DieCutLabel>';

                    label = dymo.label.framework.openLabelXml(testAddressLabelXml);

                    printButton.disabled = false;
                }

                // reset page
                resetButton.onclick = function() {
                    window.location.reload();
                }

                // prints the label
                printButton.onclick = function() {
                    try {
                        if (!label) {
                            alert("Load label before printing");
                            return;
                        }

                        //alert(printersSelect.value);
                        label.print(printersSelect.value);
                        console.log('Printed', printersSelect.value)
                        //label.print("unknown printer");
                    } catch (e) {
                        alert(e.message || e);
                    }
                }

                // load printers list on startup
                loadPrinters();
            };

            // register onload event
            if (window.addEventListener)
                window.addEventListener("load", onload, false);
            else if (window.attachEvent)
                window.attachEvent("onload", onload);
            else
                window.onload = onload;
        }());
    </script>
</body>

</html>
