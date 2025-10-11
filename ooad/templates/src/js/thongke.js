
        $('#menu-btn').click(function () {
            $('#menu').toggleClass("active");
        })



    const xValues = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
  
    new Chart("myChart", {
  
      type: "line",
      data: {
        labels: xValues,
        datasets: [{
          data: [860, 1140, 1060, 1060, 1070, 1110, 1330, 2210, 7830, 2478, 3500, 4000],
          borderColor: "#004445",
          fill: false
        }, {
          data: [1600, 1700, 1700, 1900, 2000, 2700, 4000, 5000, 6000, 7000, 6500, 6300],
          borderColor: "#2c7873",
          fill: false
        }, {
          data: [300, 700, 2000, 5000, 6000, 4000, 2000, 1000, 200, 100, 150, 300],
          borderColor: "#6fb98f",
          fill: false
        }]
      },
      options: {
        legend: { display: false }
      }
    });

    const xValue = ["Cây Phát Tài", "Cây Kim Ngân", "Cây Trầu Bà"];
    const yValue = [55, 49, 44];
    const barColors = [
      "#004445",
      "#2c7873",
      "#6fb98f",
    ];
    
    new Chart("myChartv1", {
      type: "doughnut",
      data: {
        labels: xValue,
        datasets: [{
          backgroundColor: barColors,
          data: yValue
        }]
      },
      options: {
        title: {
          display: true
        }
      }
    });
