$(document).ready(function(){ 
	$('.btn-primary').on('click', getData);
});	

function getData()
{
	var filter = {};
	// получаем данные формы	
	filter.state = $('#state').val();
	filter.number = $('#number').val();
	filter.registrationDateS = $('#registrationDateS').val();
	filter.registrationDatePo = $('#registrationDatePo').val();
	filter.endDateS = $('#endDateS').val();
	filter.endDatePo = $('#endDatePo').val();	
	filter.size = $('#size').val();
	
	var filterJson= JSON.stringify(filter);

	// отправляем данные и обрабатываем ответ
	$.ajax({
		type: "POST",
		url: 'ajax/index.php',
		data: {filter: filterJson},    
		timeout: 10000,
		success: function(json){	

			var answer = JSON.parse(json);
			
			if(answer.result == 'ok')
				showData(answer.items);
			else if(answer.result == 'error')
				alert(answer.errorMsg);
			else
				alert("Неизвестная ошибка!");
		},			
		error: function() {
			alert("Ошибка подключения к серверу!");
		}
	});		

}

function showData(items)
{
	// показываем список
	var tbodyHtml = '';	
	
	var colors = {1: 'table-dark', 3: 'table-info', 5: 'table-secondary', 6: 'table-success', 10: 'table-warning', 11: 'table-danger', 14: 'table-danger', 15: 'table-warning'};
	
	for(var key in items)
	{
		var item = items[key];
		
		var newTr = '';
		
		newTr += '<tr class="'+colors[item.idStatus]+'">';
		newTr += '<td data-field="id">'+item.id+'</td>';
		newTr += '<td data-field="statusName">'+item.statusName+'</td>';
		newTr += '<td data-field="number">'+item.number+'</td>';
		newTr += '<td data-field="registrationDate">'+item.registrationDate+'</td>';
		newTr += '<td data-field="endDate">'+item.endDate+'</td>';
		newTr += '<td data-field="productName">'+item.productName+'</td>';
		newTr += '<td data-field="applicantName">'+item.applicantName+'</td>';
		newTr += '<td data-field="manufacterName">'+item.manufacterName+'</td>';
		newTr += '<td data-field="productOrigin">'+item.productOrigin+'</td>';
		newTr += '<td data-field="objectType">'+item.objectType+'</td>';
		newTr += '</tr>';
		
		tbodyHtml += newTr;
	}
	
	$('#declaration-list tbody').html(tbodyHtml);
	
	// чтобы пользователю не нужно было скролить до результатов
	var tableTop = $('#declaration-list').offset().top;
	$(document).scrollTop(tableTop);
}
