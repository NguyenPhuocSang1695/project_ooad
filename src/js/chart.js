const DATA_COUNT = 5;
const NUMBER_CFG = {
  count: DATA_COUNT,
  min:0,
  max: 100,
};

const labels =  ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4'];
const data = {
labels: labels,
datasets: [
{
label: 'Doanh thu',
data: [50, 40, 50 , 40],
borderColor: 'rgba(255, 99, 132, 1)',
backgroundColor:'rgba(255, 99, 132, 0.5)',
},
{
label: 'Lợi nhuận',
data: [40, 50, 30,40],
borderColor: 'rgba(54, 162, 235, 1)',
backgroundColor:'rgba(54, 162, 235, 0.5)',
}
]
};
const config = {
type: 'line',
data: data,
options: {
responsive: true,
plugins: {
legend: {
position: 'top',
},
title: {
display: true,
text: 'Chart.js Line Chart'
}
},
interaction: {
  mode: 'index',
  intersect: false,
},
},
};

window.onload = function () {
const ctx = document.getElementById('visitors-chart').getContext('2d');
new Chart(ctx, config);
}; 

const ctx = document.getElementById('workPerformance').getContext('2d');
const workPerformanceChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5'],
    datasets: [{
      label: 'Hoàn thành (%)',
      data: [55, 90, 96, 85, 49],
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1
    }]
  },

});

