// Verbatim copy of the calculation script from docs/cassandra Main source  (1).docx
// (the first <script> block). Do not edit: tests/Legacy/harness.mjs runs this exact code
// to produce reference outputs for the Laravel port (BRD §25, FR-028).
	// Feasibility Submission for printing
	function submitFeasibility() {
		$("#saveButton").attr("disable","disable");
		var fid = $("#FeasibilityID").val();
		var in1 = $("#inputData1").val();
		var in2 = $("#inputData2").val();
		var out1 = $("#OUTPUT_PART1").html();
		var out2 = $("#OUTPUT_PART2").html();
		var out3 = $("#OUTPUT_PART3").html();
		var captcha_code = $("#tools_captcha_code_fs").val();
		$.ajax({
			type: "POST",
			cache: false,
			url: "https://abdullah.kw/content/endpoint/tools/submission.php?lang=ar&f=feasibility4&r="+Math.random(),
			data: { fid: fid, in1 : in1, in2 : in2, out1 : out1, out2 : out2, out3 : out3, captcha_code: captcha_code },
			success: function(html){
				$("#feasibility_result").html(html);
				setTimeout(function() { $("#saveButton").removeAttr("disable"); },500);
			}
		});
	}
	// Charting Method
	function drawChart(type,div,title,mdata) {
		if(type == "pie") {
			google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(pieDrawChart);
			function pieDrawChart() {
				if(mdata.length > 0) {
					var data = new google.visualization.DataTable();
					data.addColumn('string', 'التكلفة');
					data.addColumn('number', 'القيمة');
					data.addRows(mdata);
					// Set chart options
					var options = { 'width':350,
									'height':300,
									'title':title,
									'pieHole': 0.4,
									'pieSliceText': 'none',
									'fontName': 'Dubai',
									'colors': ['#c20208', '#dc1e25', '#e02c32', '#e73f45', '#ee565b', '#f56d71','#f9888c', '#fda3a6']
								  };
					var chart = new google.visualization.PieChart(document.getElementById(div+"Chart"));
					chart.draw(data, options);
					$("#"+div+"Div").show();
				} else {
					$("#"+div+"Div").hide();
				}
			}
		} else if(type == "line") {
			google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(barDrawChart);
			
			function barDrawChart() {
				if(mdata.length > 1) {
					var data = new google.visualization.DataTable();
					data.addColumn('string', 'السنة المالية');
					data.addColumn('number', 'إيرادات');
					data.addColumn('number', 'تكاليف');
					data.addColumn('number', 'أرباح');
					data.addRows(mdata);
					var options = {
						'width':350,
						'height':300,
						'title':title,
						'curveType': 'function',
						'legend': {position: 'bottom', textStyle: {fontSize: 11}},
						'fontName': 'Dubai',
						'colors': ['#fda3a6','#e73f45','#c20208']
					};
					var chart = new google.visualization.AreaChart(document.getElementById(div+"Chart"));
					chart.draw(data, options);
					$("#"+div+"Div").show();
				} else {
					$("#"+div+"Div").hide();
				}
			}
		}
	}
	// General Stuff
	function removeElement(id) {
		$("#"+id).remove();
		fieldsUpdated();
	}
	function mustBeInt(id,v) {
		if($.isNumeric(v)) {
		} else {
			$("#"+id).val("");
		}
		fieldsUpdated();
	}
	 function otherRemoveElement(id) {
		$("#"+id).remove();
		otherFieldsUpdated();
	}
	function otherMustBeInt(id,v) {
		if($.isNumeric(v)) {
		} else {
			$("#"+id).val("");
		}
		otherFieldsUpdated();
	}
	function hideTabs() {
		$("#Project_Tab").hide();
		$("#Financial_Tab").hide();
		$("#Market_Tab").hide();
		$("#SWOT_Tab").hide();
		$("#Project_Arrow").html("&#x25CB;");
		$("#Financial_Arrow").html("&#x25CB;");
		$("#Market_Arrow").html("&#x25CB;");
		$("#SWOT_Arrow").html("&#x25CB;");
	}
	function toggleTab(id) {
		if($("#"+id+"_Tab").is(":visible")) {
			$("#"+id+"_Tab").hide();
			$("#"+id+"_Arrow").html("&#x25CB;");
		} else {
			hideTabs();
			$("#"+id+"_Tab").show();
			$("#"+id+"_Arrow").html("&#x25CF;");                    
		}
	}
	// ================================================================= BEG: Fields Updated()
	function fieldsUpdated() {
		
		feasibilityEdited();
		
		// General Vars
		var i = c = t = ti = 0;
		var OD = [];
		var text_temp = "";
		var text2_temp = "";
		var dataJSON = {};
		OD["AverageProfit"] = 0;
		OD["CapitalRecovery"] = 0;
		OD["TotalSales"] = 0;
		OD["AverageSales"] = 0;
		OD["MarketShare"] = 0;
		// General stuff
		$("table[name=debtPaymentFixedCostElement]").each(function() { $(this).remove() });
		$("table[name=debtOverflowSetupCostElement]").each(function() { $(this).remove() });
		var _cn = ($("#currencyName").val().length > 0 && $("#currencyName").val().length < 6) ? $("#currencyName").val() : "د.ك.";
		var _cd = ($("#currencyDecimals").val() > -1 && $("#currencyDecimals").val() < 4 && $("#currencyDecimals").val() != "") ? $("#currencyDecimals").val() : 2;
		var _cvat = ($("#currencyVAT").val() > -1 && $("#currencyVAT").val() < 100) ? ($("#currencyVAT").val()/100) : 0;
		var _sy = ($("#studyYears").val() > 0 && $("#studyYears").val() < 16 && $("#studyYears").val() != "") ? $("#studyYears").val() : 3;
		var marketValue = (!isNaN($("#marketValue").val())) ? $("#marketValue").val() : 0;
		
		dataJSON["_cn"] = _cn;
		dataJSON["_cd"] = _cd;
		dataJSON["_cvat"] = _cvat;
		dataJSON["_sy"] = _sy;
		dataJSON["marketValue"] = marketValue;
		// Liabilities
		var liabilityElement = [];
		dataJSON["Liability"] = {}
		OD["TotalBorrowed"] = 0;
		OD["TotalLiabilities"] = 0;
		i = 0; $('input[name=debtBank]').each(function() { if(typeof liabilityElement[i] == 'undefined') { liabilityElement[i] = []; } liabilityElement[i]["Bank"] = $(this).val(); i++; });
		i = 0; $('input[name=debtAmount]').each(function() { if(typeof liabilityElement[i] == 'undefined') { liabilityElement[i] = []; } liabilityElement[i]["Amount"] = Number($(this).val()); i++; });    
		i = 0; $('input[name=debtInterest]').each(function() { if(typeof liabilityElement[i] == 'undefined') { liabilityElement[i] = []; } liabilityElement[i]["Interest"] = Number($(this).val()); i++; });
		i = 0; $('input[name=settlementYears]').each(function() { if(typeof liabilityElement[i] == 'undefined') { liabilityElement[i] = []; } liabilityElement[i]["Period"] = liabilityElement[i]["Remaining"] = Number($(this).val()); i++; });
		$("#outputDebts").html("لا يوجد بيانات حتى الآن..");
		for(i=0; i<liabilityElement.length; i++) {
			if(liabilityElement[i]["Bank"].length > 0 && !isNaN(liabilityElement[i]["Amount"]) && !isNaN(liabilityElement[i]["Interest"]) && !isNaN(liabilityElement[i]["Period"]) && liabilityElement[i]["Period"] != 0) {
				dataJSON["Liability"][i] = {};
				dataJSON["Liability"][i]["Bank"] = liabilityElement[i]["Bank"];
				dataJSON["Liability"][i]["Amount"] = liabilityElement[i]["Amount"];
				dataJSON["Liability"][i]["Interest"] = liabilityElement[i]["Interest"];
				dataJSON["Liability"][i]["Period"] = liabilityElement[i]["Period"];
				
				OD["TotalBorrowed"] += parseFloat(liabilityElement[i]["Amount"]);
				if(liabilityElement[i]["Interest"] > 0 ) {
					liabilityElement[i]["Payment"] = ((liabilityElement[i]["Interest"]/100/12) * liabilityElement[i]["Amount"]) / (1-Math.pow((1+(liabilityElement[i]["Interest"]/100/12)),(-1*(liabilityElement[i]["Period"]*12))));
				} else {
					liabilityElement[i]["Payment"] = liabilityElement[i]["Amount"] / liabilityElement[i]["Period"] / 12;
				}
				if(liabilityElement[i]["Payment"] > 0) { addDebtPaymentFixedCost(liabilityElement[i]["Bank"],liabilityElement[i]["Payment"].toFixed(currencyDecimals),liabilityElement[i]["Period"]); }
				liabilityElement[i]["Payable"] = liabilityElement[i]["Payment"] * 12 * liabilityElement[i]["Period"];
				OD["TotalLiabilities"] += liabilityElement[i]["Payable"];
				liabilityElement[i]["Payment_woInterest"] = liabilityElement[i]["Amount"] / 12 / liabilityElement[i]["Period"];
				if(i==0) $("#outputDebts").html("");
				$("#outputDebts").append(''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+liabilityElement[i]["Bank"]+'<br>('+liabilityElement[i]["Interest"]+'% - '+ liabilityElement[i]["Period"] +' سنوات)</td>'+
							'<td class="tools_0024">'+readableAmount(liabilityElement[i]["Amount"],_cd,_cn)+'<br>('+readableAmount(liabilityElement[i]["Payable"],_cd,_cn)+')</td>'+
						'</tr>'+
					'</table>'+
				'');
			} else {
				liabilityElement.splice(i,1);
				i--;
			}
		}
		$("#totalDebts").html(readableAmount(OD["TotalLiabilities"],_cd,""));
		$("#totalDebts_currency").html(_cn);
		$("#outputTotalDebts").html(readableAmount(OD["TotalLiabilities"],_cd,""));
		 $("#outputTotalDebts_currency").html(_cn); 
		
		// Assets
		var assetElement = [];
		dataJSON["Asset"] = {}
		var chartAssetsDistribution = [];
		OD["TotalAssets"] = 0;
		OD["UnassignedLiabilities"] = 0;
		OD["Self_Financing"] = 0;
		i = 0; $('input[name=setupCostName]').each(function() { if(typeof assetElement[i] == 'undefined') { assetElement[i] = []; } assetElement[i]["Name"] = $(this).val(); i++; });
		i = 0; $('input[name=setupCostValue]').each(function() { if(typeof assetElement[i] == 'undefined') { assetElement[i] = []; } assetElement[i]["Value"] = assetElement[i]["AccumulatedValue"] = Number($(this).val()); i++; });    
		i = 0; $('input[name=setupCostIsCash]').each(function() { if(typeof assetElement[i] == 'undefined') { assetElement[i] = []; } assetElement[i]["IsCash"] = Number($(this).val()); i++; });    
		i = 0; $('input[name=setupCostLifeSpan]').each(function() { if(typeof assetElement[i] == 'undefined') { assetElement[i] = []; } assetElement[i]["LifeSpan"] = assetElement[i]["LifeToGo"] = Number($(this).val()); i++; });
		i = 0; $('input[name=setupCostDepreciation]').each(function() { if(typeof assetElement[i] == 'undefined') { assetElement[i] = []; } assetElement[i]["DepreciationRatio"] = Number($(this).val()); i++; });
		i = 0; $('input[name=setupCostIncludesVAT]').each(function() { if(typeof assetElement[i] == 'undefined') { assetElement[i] = []; } assetElement[i]["IncludesVAT"] = false; if($(this).is(":checked")) { assetElement[i]["IncludesVAT"] = true; } i++; });
		$("#outputSetupCosts").html("لا يوجد بيانات حتى الآن..");
		for(i=0; i<assetElement.length; i++) {
			if(assetElement[i]["Name"].length > 0 && !isNaN(assetElement[i]["Value"])) {
				if(assetElement[i]["IsCash"] == 0) {
					dataJSON["Asset"][i] = {};
					dataJSON["Asset"][i]["Name"] = assetElement[i]["Name"];
					dataJSON["Asset"][i]["Value"] = assetElement[i]["Value"];
					dataJSON["Asset"][i]["Depreciation"] = assetElement[i]["DepreciationRatio"];
					dataJSON["Asset"][i]["LifeSpan"] = assetElement[i]["LifeSpan"];
					dataJSON["Asset"][i]["IncludesVAT"] = assetElement[i]["IncludesVAT"];
				}
				OD["TotalAssets"] += parseFloat(assetElement[i]["Value"]);
				assetElement[i]["DepreciationValue"] = 0;
				assetElement[i]["VATofDepreciation"] = 0;
				chartAssetsDistribution.push([assetElement[i]["Name"],assetElement[i]["Value"]]);
				if(i==0) $("#outputSetupCosts").html("");
				$("#outputSetupCosts").append(''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+assetElement[i]["Name"]+'<br>('+assetElement[i]["LifeSpan"]+' سنوات)</td>'+
							'<td class="tools_0024">'+readableAmount(assetElement[i]["Value"],_cd,_cn)+'<br>('+readableAmount(assetElement[i]["DepreciationRatio"],_cd,"")+'%)</td>'+
						'</tr>'+
					'</table>'+
				'');
			} else {
				assetElement.splice(i,1);
				i--;
			}
		}
		if(assetElement.length > 0) { drawChart("pie","chartSetupCosts","",chartAssetsDistribution); } else { $("#chartSetupCostsDiv").hide(); }
		if(OD["TotalBorrowed"] > OD["TotalAssets"]) {
			OD["UnassignedLiabilities"] = OD["TotalBorrowed"] - OD["TotalAssets"];
			assetElement[i] = [];
			assetElement[i]["Name"] = "مبالغ غير مستغلة";
			assetElement[i]["Value"] = assetElement[i]["AccumulatedValue"] = OD["UnassignedLiabilities"];
			assetElement[i]["DepreciationValue"] = assetElement[i]["LifeSpan"] = assetElement[i]["LifeToGo"] = assetElement[i]["DepreciationRatio"] = assetElement[i]["VATofDepreciation"] = assetElement[i]["VATofDepreciation"] = 0;
			assetElement[i]["IncludesVAT"] = false;
			assetElement[i]["IsCash"] = 1;
			addDebtOverflowSetupCost(assetElement[i]["Name"],assetElement[i]["Value"]);
			i++;
			OD["TotalAssets"] += OD["UnassignedLiabilities"];
		}                                                                                        
		OD["Self_Financing"] = ((OD["TotalAssets"] - OD["TotalBorrowed"]) > 0) ? (OD["TotalAssets"] - OD["TotalBorrowed"]) : 0;
		// Hang on ..                                                        
		$("#totalSetupCost").html(readableAmount(OD["TotalAssets"],_cd,""));
		$("#totalSetupCost_currency").html(_cn);
		$("#outputTotalSetupCost").html(readableAmount(OD["TotalAssets"],_cd,""));
		 $("#outputTotalSetupCost_currency").html(_cn); 
		$("#totalCapital_value").html(readableAmount(OD["TotalAssets"],_cd,""));
		$("#totalCapital_currency").html(_cn);
		$("#CapitalDebts_value").html(readableAmount(OD["TotalLiabilities"],_cd,""));
		 $("#CapitalDebts_currency").html(_cn);                                                                                          
		$("#CapitalPrivate_value").html(readableAmount(OD["Self_Financing"],_cd,""));
		 $("#CapitalPrivate_currency").html(_cn);  
		// Hang on ..
		if(OD["TotalBorrowed"] > 0 || OD["TotalAssets"] > 0) {
			var chartCapitalDistribution = [];
			chartCapitalDistribution.push(["مديونية",OD["TotalBorrowed"]]);
			chartCapitalDistribution.push(["تمويل خاص",OD["Self_Financing"]]);
			drawChart("pie","chartCapitalDistribution","",chartCapitalDistribution);
		} else {
			$("#chartCapitalDistributionDiv").hide();
		}                                                                                      
		 // Employees
		var employeeElement = [];
		dataJSON["Employee"] = {}
		OD["TotalSalaries"] = 0;
		i = 0; $('input[name=employeeTitle]').each(function() { if(typeof employeeElement[i] == 'undefined') { employeeElement[i] = []; } employeeElement[i]["Title"] = $(this).val(); i++; });
		i = 0; $('input[name=employeeTotal]').each(function() { if(typeof employeeElement[i] == 'undefined') { employeeElement[i] = []; } employeeElement[i]["Total"] = Number($(this).val()); i++; });    
		i = 0; $('input[name=employeeSalary]').each(function() { if(typeof employeeElement[i] == 'undefined') { employeeElement[i] = []; } employeeElement[i]["Salary"] = Number($(this).val()); i++; });
		$("#outputEmployees").html("لا يوجد بيانات حتى الآن..");
		for(i=0; i<employeeElement.length; i++) {
			if(employeeElement[i]["Title"].length > 0 && !isNaN(employeeElement[i]["Salary"]) && !isNaN(employeeElement[i]["Total"])) {
				dataJSON["Employee"][i] = {};
				dataJSON["Employee"][i]["Title"] = employeeElement[i]["Title"];
				dataJSON["Employee"][i]["Total"] = employeeElement[i]["Total"];
				dataJSON["Employee"][i]["Salary"] = employeeElement[i]["Salary"];
				
				OD["TotalSalaries"] += parseFloat(employeeElement[i]["Salary"] * employeeElement[i]["Total"]);
				if(i == 0) $("#outputEmployees").html("");
				$("#outputEmployees").append(''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+employeeElement[i]["Total"]+' × '+employeeElement[i]["Title"]+'</td>'+
							'<td class="tools_0024">'+readableAmount(employeeElement[i]["Salary"],_cd,_cn)+'</td>'+
						'</tr>'+
					'</table>'+
				'');
			} else {
				employeeElement.splice(i,1);
				i--;
			}
		}
		$("#totalSalaries").html(readableAmount(OD["TotalSalaries"],_cd,""));
		$("#totalSalaries_currency").html(_cn);
		$("#outputTotalSalaries").html(readableAmount(OD["TotalSalaries"],_cd,""));
		 $("#outputTotalSalaries_currency").html(_cn); 
			 
		
		 // Fixed Costs
		var fcElement = [];
		dataJSON["FC"] = {};
		OD["TotalFC"] = 0;
		OD["TotalFCPaidVAT"] = 0;
		i = 0; $('input[name=fixedCostName]').each(function() { if(typeof fcElement[i] == 'undefined') { fcElement[i] = []; } fcElement[i]["Name"] = $(this).val(); i++; });
		i = 0; $('input[name=fixedCostPaymentPeriod]').each(function() { if(typeof fcElement[i] == 'undefined') { fcElement[i] = []; } fcElement[i]["Period"] = fcElement[i]["Remaining"] = Number($(this).val()); i++; });    
		i = 0; $('input[name=fixedCostAmount]').each(function() { if(typeof fcElement[i] == 'undefined') { fcElement[i] = []; } fcElement[i]["Amount"] = Number($(this).val()); i++; });
		i = 0; $('input[name=fixedCostIncludesVAT]').each(function() { if(typeof fcElement[i] == 'undefined') { fcElement[i] = []; } fcElement[i]["IncludesVAT"] = false; if($(this).is(":checked")) { fcElement[i]["IncludesVAT"] = true; } i++; });
		i = 0; $('input[name=fixedCostIsDebtPayment]').each(function() { if(typeof fcElement[i] == 'undefined') { fcElement[i] = []; } fcElement[i]["IsDebtPayment"] = Number($(this).val()); i++; });
		$("#outputFixedCosts").html("لا يوجد بيانات حتى الآن..");
		for(i=0; i<fcElement.length; i++) {
			if(fcElement[i]["Name"].length > 0 && !isNaN(fcElement[i]["Amount"]) && !isNaN(fcElement[i]["Period"])) {
				if(fcElement[i]["IsDebtPayment"] == 0) {
					dataJSON["FC"][i] = {};
					dataJSON["FC"][i]["Name"] = fcElement[i]["Name"];
					dataJSON["FC"][i]["Amount"] = fcElement[i]["Amount"];
					dataJSON["FC"][i]["IncludesVAT"] = fcElement[i]["IncludesVAT"];
				}
				OD["TotalFC"] += parseFloat(fcElement[i]["Amount"]);
				fcElement[i]["VATAmount"] = (fcElement[i]["IncludesVAT"]) ? (fcElement[i]["Amount"] * _cvat) : 0;
				OD["TotalFCPaidVAT"] += fcElement[i]["VATAmount"];
				text_temp = (fcElement[i]["Period"] != 0) ? (fcElement[i]["Period"] + " سنوات") : "مدى الحياة" ;
				if(i == 0) $("#outputFixedCosts").html("");
				$("#outputFixedCosts").append(''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+fcElement[i]["Name"]+'<br>('+text_temp+')</td>'+
							'<td class="tools_0024">'+readableAmount(fcElement[i]["Amount"],_cd,_cn)+'<br>('+readableAmount(fcElement[i]["VATAmount"],_cd,_cn)+')</td>'+
						'</tr>'+
					'</table>'+
				'');
			} else {
				fcElement.splice(i,1);
				i--;
			}
		}
		$("#totalFixedCosts").html(readableAmount(OD["TotalFC"],_cd,""));
		$("#totalFixedCosts_currency").html(_cn);
		$("#outputTotalFixedCosts").html(readableAmount(OD["TotalFC"],_cd,""));
		 $("#outputTotalFixedCosts_currency").html(_cn); 
							 
		 // Products and Sales
		var productElement = [];
		dataJSON["Product"] = {};
		OD["TotalRevenue_wTax"] = 0;
		OD["TotalRevenue_woTax"] = 0;
		OD["TotalUnitsSold"] = 0;
		OD["TotalVC_wTax"] = 0;
		OD["TotalVC_woTax"] = 0;
		i = 0; $('input[name=revenueName]').each(function() { if(typeof productElement[i] == 'undefined') { productElement[i] = []; } productElement[i]["Name"] = $(this).val(); i++; });
		i = 0; $('input[name=revenueVariableCost]').each(function() { if(typeof productElement[i] == 'undefined') { productElement[i] = []; } productElement[i]["VC"] = Number($(this).val()); i++; });    
		i = 0; $('input[name=revenueQuantity]').each(function() { if(typeof productElement[i] == 'undefined') { productElement[i] = []; } productElement[i]["Quantity"] = Number($(this).val()); i++; });
		i = 0; $('input[name=revenueCapacity]').each(function() { if(typeof productElement[i] == 'undefined') { productElement[i] = []; } productElement[i]["Capacity"] = Number($(this).val()); i++; });
		i = 0; $('input[name=revenueYearlyIncrease]').each(function() { if(typeof productElement[i] == 'undefined') { productElement[i] = []; } productElement[i]["YearlyIncrease"] = Number($(this).val()); i++; });
		i = 0; $('input[name=revenuePrice]').each(function() { if(typeof productElement[i] == 'undefined') { productElement[i] = []; } productElement[i]["Price"] = Number($(this).val()); i++; });
		$("#outputRevenue").html("لا يوجد بيانات حتى الآن..");
		for(i=0; i<productElement.length; i++) {
			if(productElement[i]["Name"].length > 0 && !isNaN(productElement[i]["VC"]) && !isNaN(productElement[i]["Quantity"]) && !isNaN(productElement[i]["Price"])) {
				dataJSON["Product"][i] = {};
				dataJSON["Product"][i]["Name"] = productElement[i]["Name"];
				dataJSON["Product"][i]["VC"] = productElement[i]["VC"];
				dataJSON["Product"][i]["Quantity"] = productElement[i]["Quantity"];
				dataJSON["Product"][i]["Capacity"] = productElement[i]["Capacity"];
				dataJSON["Product"][i]["YearlyIncrease"] = productElement[i]["YearlyIncrease"]; 
				dataJSON["Product"][i]["Price"] = productElement[i]["Price"]; 
				
				productElement[i]["Capacity"] = (!isNaN(productElement[i]["Capacity"]) && productElement[i]["Capacity"] > productElement[i]["Quantity"]) ? Math.round(productElement[i]["Capacity"]) : Math.round(productElement[i]["Quantity"]);
				productElement[i]["Revenue_woTax"] = productElement[i]["Price"] * productElement[i]["Quantity"];
				productElement[i]["Revenue_wTax"] = productElement[i]["Revenue_woTax"] + (productElement[i]["Revenue_woTax"] * _cvat);
				OD["TotalRevenue_woTax"] += productElement[i]["Revenue_woTax"];
				OD["TotalRevenue_wTax"] += productElement[i]["Revenue_wTax"];
				productElement[i]["TotalVC_wTax"] = productElement[i]["VC"] * productElement[i]["Quantity"];
				productElement[i]["TotalVC_woTax"] = productElement[i]["TotalVC_wTax"] - (productElement[i]["TotalVC_wTax"] * _cvat);
				OD["TotalVC_wTax"] += productElement[i]["TotalVC_wTax"];
				OD["TotalVC_woTax"] += productElement[i]["TotalVC_woTax"];
				OD["TotalUnitsSold"] += productElement[i]["Quantity"];
				 if(i == 0) $("#outputRevenue").html("");
				$("#outputRevenue").append(''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+productElement[i]["Name"]+'<br>('+readableAmount(productElement[i]["Quantity"],0,"")+')<br>('+readableAmount(productElement[i]["Capacity"],0,"")+')</td>'+
							'<td class="tools_0024">'+readableAmount(productElement[i]["Price"],_cd,_cn)+'<br>('+readableAmount(productElement[i]["VC"],_cd,_cn)+')<br>('+readableAmount(productElement[i]["YearlyIncrease"],1,"")+'%)</td>'+
						'</tr>'+
					'</table>'+
				'');
			} else {
				productElement.splice(i,1);
				i--;
			}
		}
		$("#totalRevenue").html(readableAmount(OD["TotalRevenue_wTax"],_cd,""));
		$("#totalRevenue_currency").html(_cn);
		$("#outputTotalRevenue").html(readableAmount(OD["TotalRevenue_wTax"],_cd,""));
		 $("#outputTotalRevenue_currency").html(_cn);                                                                                          
		
		$("#inputData1").val(JSON.stringify(dataJSON));
		// ================================================================= BEG: OF YEARLY REPORT
		var yearFigures = [];
		var chartProfitibility = [];
		for(i = 0; i < _sy; i++) {
			yearFigures[i] = [];
			yearFigures[i]["Payment_woInterest"] = 0;
			yearFigures[i]["TotalLiabilities"] = (i == 0) ? OD["TotalBorrowed"] : yearFigures[i-1]["TotalLiabilities"] ;
			yearFigures[i]["TotalLiabsToEquity"] = (i == 0) ? 0 : yearFigures[i-1]["TotalLiabsToEquity"];
			
			yearFigures[i]["RetainedEarnings"] = (i == 0) ? 0 : yearFigures[i-1]["RetainedEarnings"];
			yearFigures[i]["TotalAssets"] = 0;
			yearFigures[i]["LongTermAssets"] = 0;
			yearFigures[i]["CashLongTermAssets"] = 0;
			yearFigures[i]["AccDepr"] = 0;
			yearFigures[i]["AccDeprToTA"] = 0;
			yearFigures[i]["ValueOfAssetsBought"] = 0;
			yearFigures[i]["ValueOfAssetsSold"] = 0;
			yearFigures[i]["VATofDepreciation"] = 0;
			yearFigures[i]["TotalFC"] = 0;
			yearFigures[i]["TotalFC_woPayments"] = 0;
			yearFigures[i]["FCPaidVAT"] = 0;
			// Revenues
			yearFigures[i]["TotalRevenueBeforeTax"] = 0;
			yearFigures[i]["TotalRevenue_wTax"] = 0;
			yearFigures[i]["TotalRevenue_woTax"] = 0;
			yearFigures[i]["TotalUnitsSold"] = 0;
			yearFigures[i]["TotalVC_wTax"] = 0;
			yearFigures[i]["TotalVC_woTax"] = 0;
			
			// Let's deal with the sales
			for(t=0; t<productElement.length; t++) {
				if(i!=0) productElement[t]["Quantity"] = ((productElement[t]["Quantity"] + (productElement[t]["Quantity"]*productElement[t]["YearlyIncrease"]/100)) > productElement[t]["Capacity"]) ? Math.round(productElement[t]["Capacity"]) : Math.round(productElement[t]["Quantity"] + (productElement[t]["Quantity"]*productElement[t]["YearlyIncrease"]/100)) ;
				productElement[t]["Revenue_woTax"] = productElement[t]["Price"] * productElement[t]["Quantity"];
				productElement[t]["Revenue_wTax"] = productElement[t]["Revenue_woTax"] + (productElement[t]["Revenue_woTax"] * _cvat);
				yearFigures[i]["TotalRevenue_woTax"] += (productElement[t]["Revenue_woTax"] * 12);
				yearFigures[i]["TotalRevenue_wTax"] += (productElement[t]["Revenue_wTax"] * 12);
				productElement[t]["TotalVC_wTax"] = productElement[t]["VC"] * productElement[t]["Quantity"];
				productElement[t]["TotalVC_woTax"] = productElement[t]["TotalVC_wTax"] - (productElement[t]["TotalVC_wTax"] * _cvat);
				yearFigures[i]["TotalVC_wTax"] += (productElement[t]["TotalVC_wTax"] * 12);
				yearFigures[i]["TotalVC_woTax"] += (productElement[t]["TotalVC_woTax"] * 12);
				yearFigures[i]["TotalUnitsSold"] += (productElement[t]["Quantity"] * 12); 
				productElement[t]["VCwoTax"] = productElement[t]["VC"] - (productElement[t]["VC"] * _cvat);
			}
			OD["TotalSales"] += yearFigures[i]["TotalRevenue_wTax"];
			yearFigures[i]["VCPaidVAT"] = yearFigures[i]["TotalVC_wTax"] - yearFigures[i]["TotalVC_woTax"];
			 
			// Expenses
			yearFigures[i]["TotalExpensesBeforeTax"] = 0;
			yearFigures[i]["VATin"] = 0;
			yearFigures[i]["VATout"] = 0;
			yearFigures[i]["VAToutCash"] = 0;
			yearFigures[i]["PendingVAT"] = 0;
			yearFigures[i]["ProfitBeforeTax"] = 0;
			yearFigures[i]["ProfitAfterTax"] = 0;
			yearFigures[i]["AverageProfitPerSale"] = 0;
			
			// Cash-Flow
			yearFigures[i]["CashFlowFromOperating"] = 0;
			yearFigures[i]["CashFlowFromInvesting"] = 0;
			yearFigures[i]["CashFlowFromFinancing"] = 0;
			yearFigures[i]["NetCashFlow"] = 0;
			yearFigures[i]["CashFlowBeginning"] = (i==0) ? OD["UnassignedLiabilities"] : yearFigures[i-1]["CashFlowEnding"];
			yearFigures[i]["CashFlowEnding"] = 0;
			// Profitibility Stuff
			yearFigures[i]["FinalTotalExpenses"] = 0;
			yearFigures[i]["FinalTotalRevenue"] = 0;
			yearFigures[i]["FinalTotalProfit"] = 0;
			
			// Assets
			if(assetElement.length > 0) {
				for( t = 0; t < assetElement.length; t++) {
					if(assetElement[t]["LifeToGo"] > 0 || assetElement[t]["LifeSpan"] == 0) {
						assetElement[t]["LifeToGo"] = ((assetElement[t]["LifeToGo"]-1) < 0) ? 0 : (assetElement[t]["LifeToGo"]-1);
					} else {
						assetElement[t]["LifeToGo"] = assetElement[t]["LifeSpan"];
						yearFigures[i]["ValueOfAssetsSold"] += assetElement[t]["AccumulatedValue"];
						assetElement[t]["AccumulatedValue"] = assetElement[t]["Value"];
						yearFigures[i]["ValueOfAssetsBought"] += assetElement[t]["Value"];
						assetElement[t]["LifeToGo"]--;
					}
					if(assetElement[t]["IsCash"]) yearFigures[i]["CashLongTermAssets"] += assetElement[t]["AccumulatedValue"];
					yearFigures[i]["LongTermAssets"] += assetElement[t]["AccumulatedValue"];
					assetElement[t]["DepreciationValue"] = assetElement[t]["AccumulatedValue"] * assetElement[t]["DepreciationRatio"] / 100;
					assetElement[t]["VATofDepreciation"] = (assetElement[t]["IncludesVAT"]) ? (assetElement[t]["DepreciationValue"] * _cvat) : 0;
					yearFigures[i]["VATofDepreciation"] += assetElement[t]["VATofDepreciation"];
					yearFigures[i]["AccDepr"] += assetElement[t]["DepreciationValue"];
					assetElement[t]["AccumulatedValue"] = assetElement[t]["AccumulatedValue"] - assetElement[t]["DepreciationValue"]; 
				} 
				yearFigures[i]["AccDeprToTA"] = ((yearFigures[i]["LongTermAssets"]) > 0) ? ((yearFigures[i]["AccDepr"]/(yearFigures[i]["LongTermAssets"])*100).toFixed(0) + "%") : "غير متوفر"; 
			}
			
			// Preparing for Income Statement
			yearFigures[i]["TotalRevenueBeforeTax"] = yearFigures[i]["ValueOfAssetsSold"]+yearFigures[i]["TotalRevenue_wTax"]-yearFigures[i]["TotalVC_woTax"];
			yearFigures[i]["VATin"] = yearFigures[i]["TotalRevenue_wTax"] - yearFigures[i]["TotalRevenue_woTax"];
			
			// Income Statement
			if(i == 0) { $("#Statements").html(""); } else { $("#Statements").append('<div class="pg_breaker">'); }
			$("#Statements").append('<div class="tools_0012_008">السنة المالية ('+(i+1)+')</div>');
			$("#Statements").append(''+
				'<table cellpadding="0" cellspacing="0" class="tools_0016">'+
					'<tr>'+ 
						'<td class="tools_0012_006">بيانات الدخل</td>'+
						'<td class="tools_0012_007">القيمة</td>'+
					'</tr>'+
				'</table>'+
				'<div class="tools_0012_005">الإيرادات</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">إيرادات تشغيلية</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["TotalRevenue_woTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">تكلفة مواد أولية</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["TotalVC_woTax"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">بيع أصول مستهلكة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["ValueOfAssetsSold"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
			   '<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">ضريبة محصلة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["VATin"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي الإيرادات</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["TotalRevenueBeforeTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+                   
				'<div class="tools_0012_005">المصروفات</div>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">شراء أصول جديدة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["ValueOfAssetsBought"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">تكاليف استهلاك أصول</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["AccDepr"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">رواتب موظفين</td>'+
						'<td class="tools_0012_001">'+readableAmount((OD["TotalSalaries"]*12),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+ 
			'');
			// Income Statement Fixed Costs
			if(fcElement.length >0) {
				for( t = 0; t < fcElement.length; t++) {
					if(fcElement[t]["Remaining"] > 0 || fcElement[t]["Period"] == 0) {
						fcElement[t]["Remaining"] = ((fcElement[t]["Remaining"]-1) < 0) ? 0 : (fcElement[t]["Remaining"]-1);
						
						yearFigures[i]["FCPaidVAT"] += (fcElement[t]["VATAmount"]*12);
						yearFigures[i]["TotalFC"] += ((fcElement[t]["Amount"] - fcElement[t]["VATAmount"])*12);
						yearFigures[i]["TotalFC_woPayments"] += (fcElement[t]["IsDebtPayment"] == 1) ? 0 : ((fcElement[t]["Amount"] - fcElement[t]["VATAmount"])*12);
						$("#Statements").append(''+                                                             
							'<table cellpadding="0" cellspacing="0" class="width100pr">'+
								'<tr>'+ 
									'<td class="tools_0011_001">'+fcElement[t]["Name"]+'</td>'+
									'<td class="tools_0012_001">'+readableAmount(((fcElement[t]["Amount"]-fcElement[t]["VATAmount"])*12),_cd,_cn)+'</td>'+
								'</tr>'+
							'</table>'+
						'');
					}
				}
			}
			// Income Statement VAT
			yearFigures[i]["VATout"] =  yearFigures[i]["FCPaidVAT"] + yearFigures[i]["VCPaidVAT"];
			$("#Statements").append(''+                                                             
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">ضريبة مدفوعة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["VATout"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
			'');
			// Income Statement Total Expenses
			yearFigures[i]["TotalExpensesBeforeTax"] = yearFigures[i]["ValueOfAssetsBought"] + yearFigures[i]["AccDepr"] + yearFigures[i]["TotalFC"] + yearFigures[i]["VATout"] + (OD["TotalSalaries"]*12);
			$("#Statements").append(''+  
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي المصروفات</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["TotalExpensesBeforeTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+                                
			'');
			// Finalizing Income Statement
			yearFigures[i]["PendingVAT"] = ((yearFigures[i]["VATin"] - yearFigures[i]["VATout"]) > 0) ? (yearFigures[i]["VATin"] - yearFigures[i]["VATout"]) : 0;
			yearFigures[i]["ProfitBeforeTax"] = yearFigures[i]["TotalRevenueBeforeTax"] - yearFigures[i]["TotalExpensesBeforeTax"];
			yearFigures[i]["ProfitAfterTax"] = yearFigures[i]["ProfitBeforeTax"] - yearFigures[i]["PendingVAT"];
			$("#Statements").append(''+ 
				'<div class="tools_0012_005">الأرباح</div>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">أرباح قبل الضريبة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["ProfitBeforeTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">ضريبة القيمة المضافة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["PendingVAT"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي الأرباح</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["ProfitAfterTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+    
			'');                                
			// Cash-flow Statement                                
			yearFigures[i]["CashFlowFromOperating"] = yearFigures[i]["TotalRevenue_woTax"] + yearFigures[i]["VATin"] - yearFigures[i]["TotalFC_woPayments"] - (OD["TotalSalaries"]*12) - yearFigures[i]["TotalVC_woTax"] - yearFigures[i]["VATout"] - yearFigures[i]["PendingVAT"];
			yearFigures[i]["CashFlowFromInvesting"] = yearFigures[i]["ValueOfAssetsSold"] - yearFigures[i]["ValueOfAssetsBought"];
			yearFigures[i]["CashFlowFromFinancing"] = (yearFigures[i]["TotalFC"] - yearFigures[i]["TotalFC_woPayments"]) * -1;
			yearFigures[i]["NetCashFlow"] = yearFigures[i]["CashFlowFromOperating"] + yearFigures[i]["CashFlowFromInvesting"] + yearFigures[i]["CashFlowFromFinancing"];
			yearFigures[i]["CashFlowEnding"] = yearFigures[i]["CashFlowBeginning"] + yearFigures[i]["NetCashFlow"];
			$("#Statements").append('<div class="pg_breaker"></div>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0016">'+
					'<tr>'+ 
						'<td class="tools_0012_006">بيانات التدفق النقدي</td>'+
						'<td class="tools_0012_007">القيمة</td>'+
					'</tr>'+
				'</table>'+
				'<div class="tools_0012_005">من العمليات التشغيلية</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">إيرادات تشغيلية</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["TotalRevenue_woTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">تكلفة مواد أولية</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["TotalVC_woTax"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">تكاليف تشغيلية</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["TotalFC_woPayments"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">رواتب موظفين</td>'+
						'<td class="tools_0012_001">'+readableAmount((OD["TotalSalaries"]*-12),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				  '<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">ضريبة محصلة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["VATin"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+                                                     
				 '<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">ضريبة مدفوعة</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["VATout"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">ضريبة القيمة المضافة</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["PendingVAT"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				  '<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["CashFlowFromOperating"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+                                                                                    
				'<div class="tools_0012_005">من العمليات الاستثمارية</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">شراء أصول جديدة</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["ValueOfAssetsBought"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">بيع أصول مستهلكة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["ValueOfAssetsSold"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["CashFlowFromInvesting"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+                                                                      
				'<div class="tools_0012_005">من العمليات التمويلية</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">أقساط مطلوبات</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["CashFlowFromFinancing"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["CashFlowFromFinancing"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
									
				'<div class="tools_0012_005">صافي التدفق النقدي</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">السيولة هذه السنة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["NetCashFlow"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">السيولة في بداية السنة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["CashFlowBeginning"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">السيولة في نهاية السنة</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["CashFlowEnding"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+              
			'');
			
			// Preparing for Balance Sheet
			if(liabilityElement.length >0) {
				for( t = 0; t < liabilityElement.length; t++) {
					if(liabilityElement[t]["Remaining"] > 0 || liabilityElement[t]["Period"] == 0) {
						liabilityElement[t]["Remaining"] = ((liabilityElement[t]["Remaining"]-1) < 0) ? 0 : (liabilityElement[t]["Remaining"]-1);
						yearFigures[i]["Payment_woInterest"] += (liabilityElement[t]["Payment_woInterest"]*12);
					}
				}
			}
			yearFigures[i]["TotalLiabsToEquity"] += yearFigures[i]["Payment_woInterest"]
			yearFigures[i]["TotalLiabilities"] = ((yearFigures[i]["TotalLiabilities"] - yearFigures[i]["Payment_woInterest"]) < 0) ? 0 : (yearFigures[i]["TotalLiabilities"]- yearFigures[i]["Payment_woInterest"]);
			yearFigures[i]["TotalAssets"] = yearFigures[i]["CashFlowEnding"] + yearFigures[i]["LongTermAssets"] - yearFigures[i]["AccDepr"] - yearFigures[i]["CashLongTermAssets"];
			yearFigures[i]["TotalOwnersEquity"] = yearFigures[i]["RetainedEarnings"] + OD["Self_Financing"] + yearFigures[i]["ProfitAfterTax"]  + (yearFigures[i]["CashFlowFromInvesting"] * -1) + yearFigures[i]["TotalLiabsToEquity"];
			
			// Balance Sheet
		   $("#Statements").append('<div class="pg_breaker"></div>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0016">'+
					'<tr>'+ 
						'<td class="tools_0012_006">ورقة الموازنة</td>'+
						'<td class="tools_0012_007">القيمة</td>'+
					'</tr>'+
				'</table>'+
				'<div class="tools_0012_005">الأصول</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">اصول المدى القصير</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["CashFlowEnding"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">أصول طويلة المدى</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["LongTermAssets"]-yearFigures[i]["CashLongTermAssets"]),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">استهلاك أصول طويلة المدى</td>'+
						'<td class="tools_0012_001">'+readableAmount((yearFigures[i]["AccDepr"]*-1),_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي الاصول</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["TotalAssets"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+           
				'<div class="tools_0012_005">المطلوبات</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">مطلوبات طويلة المدى</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["TotalLiabilities"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي المطلوبات</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["TotalLiabilities"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<div class="tools_0012_005">حقوق الملاك</div>'+                      
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">أرباح</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["ProfitAfterTax"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">أرباح متراكمة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["RetainedEarnings"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">أصول مطلوبة إلى مملوكة</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["TotalLiabsToEquity"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+ 
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">صافي الاستثمار في الأصول</td>'+
						'<td class="tools_0012_001">'+readableAmount(yearFigures[i]["CashFlowFromInvesting"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+  
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+ 
						'<td class="tools_0011_001">رأس المال الشخصي</td>'+
						'<td class="tools_0012_001">'+readableAmount(OD["Self_Financing"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي حقوق الملاك</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["TotalOwnersEquity"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
			   '<table cellpadding="0" cellspacing="0" class="tools_0012_002">'+
					'<tr>'+ 
						'<td class="tools_0012_003">اجمالي المطلوبات و حقوق الملاك</td>'+
						'<td class="tools_0012_004">'+readableAmount(yearFigures[i]["TotalOwnersEquity"]+yearFigures[i]["TotalLiabilities"],_cd,_cn)+'</td>'+
					'</tr>'+
				'</table>'+
			'');
			// Profitibility
			yearFigures[i]["FinalTotalExpenses"] = yearFigures[i]["TotalVC_woTax"] + yearFigures[i]["TotalExpensesBeforeTax"] + yearFigures[i]["PendingVAT"];
			yearFigures[i]["FinalTotalRevenue"] = yearFigures[i]["TotalRevenueBeforeTax"] + yearFigures[i]["TotalVC_woTax"];
			yearFigures[i]["FinalTotalProfit"] = yearFigures[i]["FinalTotalRevenue"] - yearFigures[i]["FinalTotalExpenses"];
			if(yearFigures[i]["FinalTotalRevenue"] > 0) chartProfitibility.push([(i+1).toString(),yearFigures[i]["FinalTotalRevenue"],yearFigures[i]["FinalTotalExpenses"],yearFigures[i]["FinalTotalProfit"]]);
			text_temp = (!isNaN(yearFigures[i]["FinalTotalExpenses"]/yearFigures[i]["FinalTotalRevenue"]) && yearFigures[i]["FinalTotalRevenue"] != 0) ? readableAmount((yearFigures[i]["FinalTotalExpenses"]/yearFigures[i]["FinalTotalRevenue"]*100),0,"") + "%" : "غير متوفر";
			text2_temp = (!isNaN(yearFigures[i]["FinalTotalProfit"]/yearFigures[i]["FinalTotalRevenue"]) && yearFigures[i]["FinalTotalRevenue"] != 0) ? readableAmount((yearFigures[i]["FinalTotalProfit"]/yearFigures[i]["FinalTotalRevenue"]*100),0,"") + "%" : "غير متوفر";
			
			if(i == 0) $("#Profitibility").html("");
			$("#Profitibility").append(''+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+
						'<td class="tools_0012_009">'+(i+1)+'</td>'+
						'<td class="tools_0012_009">'+readableAmount(yearFigures[i]["FinalTotalExpenses"],_cd,_cn)+'<br>('+readableAmount((yearFigures[i]["FinalTotalExpenses"]/12),_cd,_cn)+')<br>('+text_temp+')</td>'+
						'<td class="tools_0012_009">'+readableAmount(yearFigures[i]["FinalTotalProfit"],_cd,_cn)+'<br>('+readableAmount((yearFigures[i]["FinalTotalProfit"]/12),_cd,_cn)+')<br>('+text2_temp+')</td>'+
					'</tr>'+
				'</table>'+
			'');
			
			// Cost-Based Pricing Strategy
			if(productElement.length > 0) {
				if(i == 0) { $("#Pricings").html(""); }
				$("#Pricings").append('<div class="tools_0025">السنة المالية '+(i+1)+'</div>');
				for(t=0; t<productElement.length; t++) {
					text_temp = (yearFigures[i]["FinalTotalProfit"]/12) - (productElement[t]["Revenue_woTax"] - productElement[t]["TotalVC_woTax"]);
					text_temp = (productElement[t]["Quantity"] > 0) ? (text_temp / productElement[t]["Quantity"]) : 0;          
					productElement[t]["BreakEvenPrice"] = ((productElement[t]["VCwoTax"] + (text_temp*(-1))) > productElement[t]["VCwoTax"] ) ? (productElement[t]["VCwoTax"] + (text_temp*(-1))) : productElement[t]["VCwoTax"];
					productElement[t]["ProfitMargin"] = (productElement[t]["BreakEvenPrice"] > 0) ? ((productElement[t]["Price"] - productElement[t]["BreakEvenPrice"]) / productElement[t]["BreakEvenPrice"] * 100) : "na";
					text_temp = (productElement[t]["ProfitMargin"] == "na") ? ((productElement[t]["Price"] > 0 && productElement[t]["Quantity"] > 0) ? "&#x221e;" : "غير متوفر") : readableAmount(productElement[t]["ProfitMargin"],0,"") + "%" ;
					if(!isNaN(productElement[t]["BreakEvenPrice"])) {
						$("#Pricings").append(''+
							'<table cellpadding="0" cellspacing="0" class="width100pr">'+
								'<tr>'+
									'<td class="tools_0012_009">'+productElement[t]["Name"]+'</td>'+
									'<td class="tools_0012_009">'+readableAmount(productElement[t]["BreakEvenPrice"],_cd,_cn)+'</td>'+
									'<td class="tools_0012_009">'+text_temp+'</td>'+
								'</tr>'+
							'</table>'+
						'');
					}
				}
			} else {
				$("#Pricings").html("لا يوجد بيانات حتى الآن..");
			}
			// KEEP THIS AT THE END OF YEAR
			yearFigures[i]["RetainedEarnings"] += yearFigures[i]["ProfitAfterTax"]  + (yearFigures[i]["CashFlowFromInvesting"] * -1);
			OD["CapitalRecovery"] = (yearFigures[i]["RetainedEarnings"] < OD["TotalAssets"]) ? OD["CapitalRecovery"]+1 : OD["CapitalRecovery"];
			
		}
		// ================================================================= END: OF YEARLY REPORT
		
		// Feasibility Figures
		$("#outputFeasibilityFigures_Years").html(_sy);
		OD["AverageProfit"] = yearFigures[_sy-1]["RetainedEarnings"] / _sy;
		$("#outputAverageProfit").html(readableAmount(OD["AverageProfit"],_cd,_cn));
		if(OD["CapitalRecovery"] != 0) {
			if(yearFigures[i-1]["RetainedEarnings"] < OD["TotalAssets"]) {
				OD["CapitalRecovery"] = "أكثر من " + readableAmount(OD["CapitalRecovery"],0,"") + " سنوات";
			} else {
				OD["CapitalRecovery"] = "خلال " + readableAmount(OD["CapitalRecovery"],0,"") + " سنوات";
			}
		} else {
			OD["CapitalRecovery"] = "غير متوفر";
		}
		$("#outputCapitalRecovery").html(OD["CapitalRecovery"]);
		OD["AverageSales"] = OD["TotalSales"] / _sy;
		if(!isNaN(marketValue) && marketValue > 0) {
			OD["MarketShare"] = OD["AverageSales"] / marketValue * 100;
			$("#outputMarketShare").html(readableAmount(OD["MarketShare"],1,"") + "%");
		} else {
			$("#outputMarketShare").html("غير متوفر");
		}
		
		// Showing Charts
		if(chartProfitibility.length > 0) { drawChart("line","chartProfitibility","",chartProfitibility); } else { $("#chartProfitibilityDiv").hide(); }
	}
	function otherFieldsUpdated() {
		feasibilityEdited();
		
		var dataJSON = {};
		var mp = 0;
		var OD = [];
		var _cn = ($("#currencyName").val().length > 0 && $("#currencyName").val().length < 6) ? $("#currencyName").val() : "د.ك.";
		var _cd = ($("#currencyDecimals").val() > -1 && $("#currencyDecimals").val() < 4 && $("#currencyDecimals").val() != "") ? $("#currencyDecimals").val() : 2;
		var _cvat = ($("#currencyVAT").val() > -1 && $("#currencyVAT").val() < 100) ? ($("#currencyVAT").val()/100) : 0;
		// Project Information
		var projectName = ($("#projectName").val().length > 0) ? $("#projectName").val() : "";
		var projectBrief = ($("#projectBrief").val() != "") ? $("#projectBrief").val() : "";
		var projectOF = ($("#projectOF").val() != "") ? $("#projectOF").val() : "";
		dataJSON["projectName"] = projectName;
		dataJSON["projectBrief"] = projectBrief;
		dataJSON["projectOF"] = projectOF;
		if(projectName != "") { 
			projectName = '<div class="tools_0002">'+projectName+'</div><div class="ArticleSeparator_Form"></div>';
		}
		if(projectBrief != "") { 
			projectBrief = '<div class="tools_0008">'+projectBrief.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div>';    
			mp = 1;
		}
		if(projectOF != "") { 
			projectOF = '<div class="tools_0008">'+projectOF.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div>';    
			mp = 1;
		}
		mp = (mp == 1) ? "<div class='pg_breaker'></div>" : "";
		$("#output_ProjectNameDesc").html(projectName+projectBrief+projectOF+mp);   
		// Owners
		var ownerElement = [];
		dataJSON["Owner"] = {};
		OD["TotalOwners"] = 0;
		OD["TotalOwnersShares"] = 0;
		i = 0; $('input[name=ownerName]').each(function() { if(typeof ownerElement[i] == 'undefined') { ownerElement[i] = []; } ownerElement[i]["Name"] = $(this).val(); i++; });
		i = 0; $('input[name=ownerShare]').each(function() { if(typeof ownerElement[i] == 'undefined') { ownerElement[i] = []; } ownerElement[i]["Share"] = Number($(this).val()); i++; });    
		var ownersOutput = "";
		for(i=0; i<ownerElement.length; i++) {
			if(ownerElement[i]["Name"].length > 0 && !isNaN(ownerElement[i]["Share"]) && ownerElement[i]["Share"] > 0) {
				dataJSON["Owner"][i] = {};
				dataJSON["Owner"][i]["Name"] = ownerElement[i]["Name"];
				dataJSON["Owner"][i]["Share"] = ownerElement[i]["Share"];
				OD["TotalOwners"]++;
				OD["TotalOwnersShares"] += ownerElement[i]["Share"];
				ownersOutput += ''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+ownerElement[i]["Name"]+'</td>'+
							'<td class="tools_0024">'+readableAmount(ownerElement[i]["Share"],2,"")+'%</td>'+
						'</tr>'+
					'</table>';
			} else {
				ownerElement.splice(i,1);
				i--;
			}
		}
		$("#totalOwners").html(readableAmount(OD["TotalOwners"],0,""));
		if(OD["TotalOwnersShares"] > 100) { $("#ErrorOwners").show();
		} else { $("#ErrorOwners").hide(); }
		if(ownerElement.length > 0) {
			ownersOutput = ''+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+
						'<td class="titleTD">'+
							'<div class="ResumeTable_Info_Title">قائمة الملاك</div>'+
						'</td>'+
						'<td class="tools_0009">'+
							'<span id="outputTotalOwners">'+readableAmount(OD["TotalOwners"],0,"")+'</span>'+
						'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0016">'+
					'<tr>'+
						'<td class="tools_0011">الاسم</td>'+
						'<td class="tools_0012">الحصة</td>'+
					'</tr>'+
				'</table>'+
				'<div id="outputOwners" class="tools_0008">'+ownersOutput+'</div>'+
				'<div class="ArticleSeparator_Form"></div>'+
				'<div class="pg_breaker"></div>';
		}
		$("#output_ProjectOwners").html(ownersOutput);
		
		// Market
		var marketName = ($("#marketName").val().length > 0) ? $("#marketName").val() : "";
		var marketValue = (!isNaN($("#marketValue").val())) ? $("#marketValue").val() : 0;
		var marketDescription = ($("#marketDescription").val() != "") ? $("#marketDescription").val() : "";
		var marketProducts = ($("#marketProducts").val() != "") ? $("#marketProducts").val() : "";
		var marketTargetSegment = ($("#marketTargetSegment").val() != "") ? $("#marketTargetSegment").val() : "";
		var marketVendors = ($("#marketVendors").val() != "") ? $("#marketVendors").val() : "";
		var marketPlan = ($("#marketPlan").val() != "") ? $("#marketPlan").val() : "";
		var competitionDescription = ($("#competitionDescription").val() != "") ? $("#competitionDescription").val() : "";
		   
		dataJSON["marketName"] = marketName;
		dataJSON["marketValue"] = marketValue;
		dataJSON["marketDescription"] = marketDescription;
		dataJSON["marketProducts"] = marketProducts;
		dataJSON["marketTargetSegment"] = marketTargetSegment;
		dataJSON["marketVendors"] = marketVendors;
		dataJSON["marketPlan"] = marketPlan;
		dataJSON["competitionDescription"] = competitionDescription;
		mp = 0;
		if(marketName != "") { 
			marketName = '<div class="tools_0002">'+marketName+'<br>حجم السوق سنويا: '+readableAmount(marketValue,_cd,_cn)+'</div><div class="ArticleSeparator_Form"></div>';    
		}
		if(marketDescription != "") { 
			marketDescription = '<div class="tools_0012_013">نبذة عن السوق ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+marketDescription.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(marketProducts != "") { 
			marketProducts = '<div class="tools_0012_013">المنتجات أو الخدمات المقدمة ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+marketProducts.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(marketTargetSegment != "") { 
			marketTargetSegment = '<div class="tools_0012_013">الشريحة المستهدفة ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+marketTargetSegment.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(marketVendors != "") { 
			marketVendors = '<div class="tools_0012_013">اعتماد السوق والموردون ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+marketVendors.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(marketPlan != "") { 
			marketPlan = '<div class="tools_0012_013">من الخطط التسويقية ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+marketPlan.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(competitionDescription != "") { 
			competitionDescription = '<div class="tools_0012_013">المنافسة ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+competitionDescription.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		mp = (mp == 1) ? "<div class='pg_breaker'></div>" : "";
		if(marketDescription == "" && marketProducts == "" && marketTargetSegment == "" && marketVendors == "" && marketPlan == "" && competitionDescription == "") { marketName = ""; }
		$("#output_Market").html(marketName+marketDescription+marketProducts+marketTargetSegment+marketVendors+marketPlan+competitionDescription+mp); 
		// Competitors
		var competitorElement = [];
		dataJSON["Competitor"] = {}
		OD["TotalCompetitors"] = 0;
		OD["TotalCompetitorsShares"] = 0;
		i = 0; $('input[name=competitorName]').each(function() { if(typeof competitorElement[i] == 'undefined') { competitorElement[i] = []; } competitorElement[i]["Name"] = $(this).val(); i++; });
		i = 0; $('input[name=competitorShare]').each(function() { if(typeof competitorElement[i] == 'undefined') { competitorElement[i] = []; } competitorElement[i]["Share"] = Number($(this).val()); i++; });    
		var competitorsOutput = "";
		for(i=0; i<competitorElement.length; i++) {
			if(competitorElement[i]["Name"].length > 0 && !isNaN(competitorElement[i]["Share"]) && competitorElement[i]["Share"] > 0) {
				dataJSON["Competitor"][i] = {};
				dataJSON["Competitor"][i]["Name"] = competitorElement[i]["Name"];
				dataJSON["Competitor"][i]["Share"] = competitorElement[i]["Share"];
				
				OD["TotalCompetitors"]++;
				OD["TotalCompetitorsShares"] += competitorElement[i]["Share"];
				competitorsOutput += ''+
					'<table cellpadding="0" cellspacing="0" class="width100pr">'+
						'<tr>'+
							'<td class="tools_0023">'+competitorElement[i]["Name"]+'</td>'+
							'<td class="tools_0024">'+readableAmount(competitorElement[i]["Share"],2,"")+'%</td>'+
						'</tr>'+
					'</table>';
			} else {
				competitorElement.splice(i,1);
				i--;
			}
		}
		$("#totalCompetitors").html(readableAmount(OD["TotalCompetitors"],0,""));
		if(OD["TotalCompetitorsShares"] > 100) { $("#ErrorCompetitors").show();
		} else { $("#ErrorCompetitors").hide(); }
		if(competitorElement.length > 0) {
			competitorsOutput = ''+
				'<table cellpadding="0" cellspacing="0" class="width100pr">'+
					'<tr>'+
						'<td class="titleTD">'+
							'<div class="ResumeTable_Info_Title">المنافسون في السوق</div>'+
						'</td>'+
						'<td class="tools_0009">'+
							'<span id="outputTotalCompetitors">'+readableAmount(OD["TotalCompetitorsShares"],0,"")+'</span>'+
						'</td>'+
					'</tr>'+
				'</table>'+
				'<table cellpadding="0" cellspacing="0" class="tools_0016">'+
					'<tr>'+
						'<td class="tools_0011">اسم المنافس</td>'+
						'<td class="tools_0012">الحصة السوقية</td>'+
					'</tr>'+
				'</table>'+
				'<div id="outputCompetitors" class="tools_0008">'+competitorsOutput+'</div>'+
				'<div class="ArticleSeparator_Form"></div>'+
				'<div class="pg_breaker"></div>';
		}
		$("#output_Competitors").html(competitorsOutput);
		// SWOT
		var swotStrengths = ($("#swotStrengths").val() != "") ? $("#swotStrengths").val() : "";
		var swotWeaknesses = ($("#swotWeaknesses").val() != "") ? $("#swotWeaknesses").val() : "";
		var swotOpportunities = ($("#swotOpportunities").val() != "") ? $("#swotOpportunities").val() : "";
		var swotThreats = ($("#swotThreats").val() != "") ? $("#swotThreats").val() : "";
		
		dataJSON["swotStrengths"] = swotStrengths;
		dataJSON["swotWeaknesses"] = swotWeaknesses;
		dataJSON["swotOpportunities"] = swotOpportunities;
		dataJSON["swotThreats"] = swotThreats;
		
		mp = 0;
		var swotTitle = '<div class="tools_0002">تحليل SWOT</div><div class="ArticleSeparator_Form"></div>';    
		if(swotStrengths != "") { 
			swotStrengths = '<div class="tools_0012_013">نقاط القوة ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+swotStrengths.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(swotWeaknesses != "") { 
			swotWeaknesses = '<div class="tools_0012_013">نقاط الضعف ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+swotWeaknesses.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(swotOpportunities != "") { 
			swotOpportunities = '<div class="tools_0012_013">الفرص المتاحة ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+swotOpportunities.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		if(swotThreats != "") { 
			swotThreats = '<div class="tools_0012_013">التهديدات المتوقعة ..</div><div class="ArticleSeparator_Form"></div><div class="tools_0008">'+swotThreats.replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div><div class="ArticleSeparator_Form"></div></div>';    
			mp = 1;
		}
		mp = (mp == 1) ? "<div class='pg_breaker'></div>" : "";
		if(swotStrengths == "" && swotWeaknesses == "" && swotOpportunities == "" && swotThreats == "") { swotTitle = ""; }
		$("#output_SWOT").html(swotTitle+swotStrengths+swotWeaknesses+swotOpportunities+swotThreats+mp);
		$("#inputData2").val(JSON.stringify(dataJSON));
	}
	// ================================================================= END: Fields Updated()
	// Assets addition methods
	function addDebtOverflowSetupCost(name,value) {
		var debtOverflowSetupCostElementId = "debtOverflowSetupCostElementId_" + (Math.random()*999999).toFixed(0);
		$("#SetupCosts").prepend(''+
			'<table id="'+debtOverflowSetupCostElementId+'" name="debtOverflowSetupCostElement" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
				   '<td class="tools_0017">'+
						'<input type="text" name="setupCostName" id="setupCostName" autocomplete="off" value="'+name+'" disabled="disabled" class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input type="number" name="setupCostValue" id="setupCostValue_'+debtOverflowSetupCostElementId+'" autocomplete="off" value="'+value+'" disabled="disabled" class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr class="tools_0003">'+
				   '<td class="tools_0017">'+
						'العمر الافتراضي (سنوات):'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<input type="number" name="setupCostLifeSpan" id="setupCostLifeSpan_'+debtOverflowSetupCostElementId+'" autocomplete="off" value="0" disabled="disabled" class="tools_0010">'+
				   '</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr class="tools_0003">'+
				   '<td class="tools_0017">'+
						'% الاستهلاك السنوي:'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<input type="number" name="setupCostDepreciation" id="setupCostDepreciation_'+debtOverflowSetupCostElementId+'" autocomplete="off" value="0" disabled="disabled" class="tools_0010">'+
				   '</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr class="tools_0003">'+
				   '<td class="tools_0017">'+
						'<input type="checkbox" name="setupCostIncludesVAT" id="setupCostIncludesVAT"> خاضعة لضريبة القيمة المضافة'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<div class="tools_0021"><input type="hidden" name="setupCostIsCash" id="setupCostIsCash" value="1"><input type="number" name="setupCost_Unassigned" id="setupCost_Unassigned_'+debtOverflowSetupCostElementId+'" value="0" disabled="disabled" class="tools_0010"></div>'+
				   '</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	function addSetupCost(n,value,l,d,vat) {
		if(vat == 1) var text_temp = "checked";
		var setupCostElementId = "setupCostElementId_" + (Math.random()*999999).toFixed(0);
		$("#SetupCosts").append(''+
			'<table id="'+setupCostElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
				   '<td class="tools_0017">'+
						'<input onkeyup="fieldsUpdated()" onblur="fieldsUpdated()" type="text" value="'+n+'" name="setupCostName" id="setupCostName" autocomplete="off" placeholder="اسم التكلفة .." class="tools_0010">'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" type="number" value="'+value+'" name="setupCostValue" id="setupCostValue_'+setupCostElementId+'" autocomplete="off" placeholder="القيمة .." class="tools_0010">'+
				   '</td>'+
				   '<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
				   '<td class="tools_0017">'+
						'العمر الافتراضي (سنوات):'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" type="number" value="'+l+'" name="setupCostLifeSpan" id="setupCostLifeSpan_'+setupCostElementId+'" autocomplete="off" placeholder="مثال: 10" class="tools_0010">'+
				   '</td>'+
				   '<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
				   '<td class="tools_0017">'+
						'% الاستهلاك السنوي:'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" type="number" value="'+d+'" name="setupCostDepreciation" id="setupCostDepreciation_'+setupCostElementId+'" autocomplete="off" placeholder="مثال: 10" class="tools_0010">'+
				   '</td>'+
				   '<td class="tools_0018">'+
				'</tr>'+
				'<tr>'+
				   '<td class="tools_0017">'+
						'<input onclick="fieldsUpdated()" type="checkbox" name="setupCostIncludesVAT" id="setupCostIncludesVAT" '+text_temp+'> خاضعة لضريبة القيمة المضافة'+
				   '</td>'+
				   '<td class="tools_0018">'+
						'<div class="tools_0021"><input type="hidden" name="setupCostIsCash" id="setupCostIsCash" value="0"><input type="number" name="setupCost_Unassigned" id="setupCost_Unassigned_'+setupCostElementId+'" value="0" disabled="disabled" class="tools_0010"></div>'+
				   '</td>'+
				   '<td onclick="removeElement(\''+setupCostElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// Liabilities addition methods
	function addDebt(b,a,i,p) {
		var debtElementId = "debtElementId_" + (Math.random()*999999).toFixed(0);
		$("#Debts").append(''+
			'<table id="'+debtElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0019">'+
						'<input onkeyup="fieldsUpdated()" onblur="fieldsUpdated()" type="text" value="'+b+'" name="debtBank" id="debtBank" autocomplete="off" placeholder="اسم البنك .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" type="number" value="'+a+'" name="debtAmount" id="debtAmount_'+debtElementId+'" autocomplete="off" placeholder="قيمة القرض .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" type="number" value="'+i+'" name="debtInterest" id="debtInterest_'+debtElementId+'" autocomplete="off" placeholder="% الفائدة السنوية .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" type="number" value="'+p+'" name="settlementYears" id="settlementYears_'+debtElementId+'" autocomplete="off" placeholder="سنوات السداد .." class="tools_0010">'+
					'</td>'+
					'<td onclick="removeElement(\''+debtElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// Owners
	function addOwner(n,s) {
		var ownerElementId = "ownerElementId_" + (Math.random()*999999).toFixed(0);
		$("#Owners").append(''+
			'<table id="'+ownerElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<input onkeyup="otherFieldsUpdated();" onblur="otherFieldsUpdated();" type="text" name="ownerName" id="ownerName" value="'+n+'" autocomplete="off" placeholder="الاسم .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input onkeyup="otherMustBeInt(this.id,this.value);" onblur="otherMustBeInt(this.id,this.value);" type="number" name="ownerShare" value="'+s+'" id="ownerShare_'+ownerElementId+'" autocomplete="off" placeholder="% .." class="tools_0010">'+
					'</td>'+
					'<td onclick="otherRemoveElement(\''+ownerElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// Competitor
	function addCompetitor(n,s) {
		var competitorElementId = "competitorElementId_" + (Math.random()*999999).toFixed(0);
		$("#Competitors").append(''+
			'<table id="'+competitorElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<input onkeyup="otherFieldsUpdated();" onblur="otherFieldsUpdated();" type="text" value="'+n+'" name="competitorName" id="competitorName" autocomplete="off" placeholder="اسم المنافس .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input onkeyup="otherMustBeInt(this.id,this.value);" onblur="otherMustBeInt(this.id,this.value);" type="number" value="'+s+'" name="competitorShare" id="competitorShare_'+competitorElementId+'" autocomplete="off" placeholder="الحصة .." class="tools_0010">'+
					'</td>'+
					'<td onclick="otherRemoveElement(\''+competitorElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// Employees
	function addEmployee(ti,s,to) {
		var employeeElementId = "employeeElementId_" + (Math.random()*999999).toFixed(0);
		$("#Employees").append(''+
			'<table id="'+employeeElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<input onkeyup="fieldsUpdated()" onblur="fieldsUpdated()" type="text" value="'+ti+'" name="employeeTitle" id="employeeTitle" autocomplete="off" placeholder="المسمى الوظيفي .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+s+'" type="number" name="employeeSalary" id="employeeSalary_'+employeeElementId+'" autocomplete="off" placeholder="الراتب .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
					'<td class="tools_0017">'+
						'عدد الموظفين في هذه المسمى:'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+to+'" type="number" name="employeeTotal" id="employeeTotal_'+employeeElementId+'" autocomplete="off" placeholder="العدد .." class="tools_0010">'+
					'</td>'+
					'<td onclick="removeElement(\''+employeeElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// Fixed costs addition methods
	function addDebtPaymentFixedCost(name,payment,period) {
		var debtPaymentFixedCostElementId = "debtPaymentFixedCostElementId_" + (Math.random()*999999).toFixed(0);
		$("#fixedCosts").prepend(''+
			'<table id="'+debtPaymentFixedCostElementId+'" name="debtPaymentFixedCostElement" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<input type="text" name="fixedCostName" id="fixedCostName" value="قسط مديونية: '+htmlspecialchars(name)+'" disabled="disabled" class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input type="number" name="fixedCostAmount" id="fixedCostAmount_'+debtPaymentFixedCostElementId+'" value="'+payment+'" disabled="disabled" class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<div class="tools_0021"><input type="hidden" name="fixedCostIsDebtPayment" id="fixedCostIsDebtPayment" value="1"><input onclick="fieldsUpdated()" type="checkbox" name="fixedCostIncludesVAT" id="fixedCostIncludesVAT"> خاضعة لضريبة القيمة المضافة</div>'+
						'سنوات الدفع لهذه التكلفة:'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input type="number" name="fixedCostPaymentPeriod" id="fixedCostPaymentPeriod_'+debtPaymentFixedCostElementId+'" value="'+period+'" disabled="disabled" class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	function addFixedCost(n,a,vat) {
		if(vat == 1) var text_temp = "checked";
		var fixedCostElementId = "fixedCostElementId_" + (Math.random()*999999).toFixed(0);
		$("#fixedCosts").append(''+
			'<table id="'+fixedCostElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<input onkeyup="fieldsUpdated()" onblur="fieldsUpdated()" type="text" value="'+n+'" name="fixedCostName" id="fixedCostName" autocomplete="off" placeholder="اسم التكلفة .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+a+'" type="number" name="fixedCostAmount" id="fixedCostAmount_'+fixedCostElementId+'" autocomplete="off" placeholder="القيمة .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
					'<td class="tools_0017">'+
						'<input onclick="fieldsUpdated()" type="checkbox" name="fixedCostIncludesVAT" id="fixedCostIncludesVAT" '+text_temp+'> خاضعة لضريبة القيمة المضافة'+
					'</td>'+
					'<td class="tools_0018">'+
						'<div class="tools_0021"><input type="hidden" name="fixedCostIsDebtPayment" id="fixedCostIsDebtPayment" value="0"><input type="number" name="fixedCostPaymentPeriod" id="fixedCostPaymentPeriod_'+fixedCostElementId+'" value="0" disabled="disabled" class="tools_0010"></div>'+
					'</td>'+
					'<td onclick="removeElement(\''+fixedCostElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// Products addition methods
	function addRevenue(n,v,q,p,c,y) {
		var revenueElementId = "revenueElementId_" + (Math.random()*999999).toFixed(0);
		$("#revenue").append(''+
			'<table id="'+revenueElementId+'" cellpadding="0" cellspacing="0" class="tools_0005">'+
				'<tr>'+
					'<td class="tools_0019">'+
						'<input onkeyup="fieldsUpdated()" onblur="fieldsUpdated()" type="text" value="'+n+'" name="revenueName" id="revenueName" autocomplete="off" placeholder="اسم المنتج .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+v+'" type="number" name="revenueVariableCost" id="revenueVariableCost_'+revenueElementId+'" autocomplete="off" placeholder="تكلفة مواد أولية .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+q+'" type="number" name="revenueQuantity" id="revenueQuantity_'+revenueElementId+'" autocomplete="off" placeholder="قدرة بيع شهرية .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+p+'" type="number" name="revenuePrice" id="revenuePrice_'+revenueElementId+'" autocomplete="off" placeholder="السعر .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0018"></td>'+
				'</tr>'+
				'<tr>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+c+'" type="number" name="revenueCapacity" id="revenueCapacity_'+revenueElementId+'" autocomplete="off" placeholder="قدرة انتاج شهرية .." class="tools_0010">'+
					'</td>'+
					'<td class="tools_0019">'+
						'<input onkeyup="mustBeInt(this.id,this.value);" onblur="mustBeInt(this.id,this.value);" value="'+y+'" type="number" name="revenueYearlyIncrease" id="revenueYearlyIncrease_'+revenueElementId+'" autocomplete="off" placeholder="% زيادة بيع سنوية .." class="tools_0010">'+
					'</td>'+
					'<td onclick="removeElement(\''+revenueElementId+'\'); return false;" class="tools_0020"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="svg svg_hl_color"  viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></td>'+
				'</tr>'+
			'</table>'+
		'');
	}
	// General methods
