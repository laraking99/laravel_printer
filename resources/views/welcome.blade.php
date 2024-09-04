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
						<div class="mb-3 mt-3">
							<label for="sampleText" class="form-label">Sample Text:</label>
							<input type="text" class="form-control" id="sampleText" placeholder="Sample Text">
						</div>
                        <div class="my-3">
                            <label class="form-label" for="printersSelect">Printer:</label>
                            <select class="form-control" id="printersSelect"></select>
                        </div>
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
	<script src="/assets/plugins/dymo/dymo.label.framework.js"></script>

	<script>
        (function()
        {
            // stores loaded label info
            var label;

            // called when the document completly loaded
            function onload()
            {
                var printersSelect = document.getElementById('printersSelect');
                var printButton = document.getElementById('printButton');
                var resetButton = document.getElementById('resetButton');
                var sampleText = document.getElementById('sampleText');

                // initialize controls
                printButton.disabled = true;
                // sampleText.disabled = true;

                // loads all supported printers into a combo box
                function loadPrinters()
                {
                    var printers = dymo.label.framework.getPrinters();
                    if (printers.length == 0)
                    {
                        alert("No DYMO printers are installed. Install DYMO printers.");
                        return;
                    }

                    for (var i = 0; i < printers.length; i++)
                    {
                        var printerName = printers[i].name;

                        var option = document.createElement('option');
                        option.value = printerName;
                        option.appendChild(document.createTextNode(printerName));
                        printersSelect.appendChild(option);

                        sampleText.disabled = false;
                    }
                }

                sampleText.onchange = function()
                {
                    var text = sampleText.value;
                    var testAddressLabelXml = `<?xml version="1.0" encoding="utf-8"?>\
                        <DieCutLabel Version="8.0" Units="twips">\
                            <PaperOrientation>Landscape</PaperOrientation>\
                            <Id>Barcode</Id>\
                            <PaperName>Barcode</PaperName>\
                            <DrawCommands>\
                                <RoundRectangle X="0" Y="0" Width="1581" Height="5040" Rx="270" Ry="270" />\
                            </DrawCommands>\
                            <ObjectInfo>\
                                <AddressObject>\
                                    <Name>Barcode</Name>\
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
                                            <String>${text}</String>\
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
                        </DieCutLabel>`;

                    label = dymo.label.framework.openLabelXml(testAddressLabelXml);

                    printButton.disabled = false;
                }

                // reset page
                resetButton.onclick = function() {
                    window.location.reload();
                }

                // prints the label
                printButton.onclick = function()
                {
                    try
                    {
                        if (!label)
                        {
                            alert("Load label before printing");
                            return;
                        }

                        //alert(printersSelect.value);
                        label.print(printersSelect.value);
                        //label.print("unknown printer");
                    }
                    catch(e)
                    {
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
        } ());
    </script>
</body>

</html>
