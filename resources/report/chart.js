// Mapping keys to human readable names
const categoryMapping = {
    'aggregate_roots': 'Aggregate Root',
    'entities': 'Entity',
    'factories': 'Factory',
    'repositories': 'Repository',
    'services': 'Service',
    'value_objects': 'Value Object',
    'interfaces': 'Interface',
    'exceptions': 'Exception',
    'uncategorized': 'Uncategorized'
};

document.addEventListener("DOMContentLoaded", function () {

    const { classes, forbidden, folder } = reportData;

    // Headline
    document.getElementById("reportFolder").textContent = `Report for ${folder}`;

    const colors = [
        "#3498db", "#e74c3c", "#1abc9c", "#95a5a6", "#2ecc71", "#f39c12", "#f1c40f", "#9b59b6", "#7f8c8d"
    ];

    // Extract keys for the charts
    const labels = Object.keys(classes).filter(key => Array.isArray(classes[key]));
    const values = labels.map(key => classes[key].length);
    const readableLabels = labels.map(key => categoryMapping[key] || key);

    // PIE CHART
    const pieChart = echarts.init(document.getElementById("pieChart"));
    const pieData = readableLabels.map((label, index) => ({
        name: label,
        value: values[index]
    }));

    const pieOption = {
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c} ({d}%)'
        },
        legend: {
            orient: 'vertical',
            left: 'left'
        },
        series: [{
            name: 'Classes',
            type: 'pie',
            radius: '50%',
            data: pieData,
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            },
            color: colors
        }]
    };
    pieChart.setOption(pieOption);

    // BAR CHART
    const barChart = echarts.init(document.getElementById("barChart"));
    
    const barOption = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: readableLabels,
            axisLabel: {
                rotate: 45
            }
        },
        yAxis: {
            type: 'value',
            minInterval: 1
        },
        series: [{
            name: 'Amount',
            type: 'bar',
            data: values,
            itemStyle: {
                color: function(params) {
                    return colors[params.dataIndex % colors.length];
                }
            }
        }]
    };
    barChart.setOption(barOption);

    // Responsive Charts
    window.addEventListener('resize', function() {
        pieChart.resize();
        barChart.resize();
    });

    createClassesTable();
});

function createClassesTable() {
    const container = document.getElementById("classesTableContainer");
    if (!container) return;

    const allClasses = [];

    Object.keys(reportData.classes).forEach(category => {
        if (Array.isArray(reportData.classes[category])) {
            const readableName = categoryMapping[category] || category;
            reportData.classes[category].forEach(className => {
                allClasses.push({
                    name: className,
                    category: readableName
                });
            });
        }
    });

    if (reportData.forbidden && reportData.forbidden.length > 0) {
        reportData.forbidden.forEach(forbiddenItem => {
            allClasses.push({
                name: forbiddenItem,
                category: 'Forbidden'
            });
        });
    }

    allClasses.sort((a, b) => a.name.localeCompare(b.name));

    const table = document.createElement("table");
    table.style.width = "100%";
    table.style.borderCollapse = "collapse";
    table.style.marginTop = "20px";

    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");
    
    const thName = document.createElement("th");
    thName.textContent = "Classname";
    thName.style.border = "1px solid #ddd";
    thName.style.padding = "10px";
    thName.style.backgroundColor = "#f5f5f5";
    thName.style.textAlign = "left";
    thName.style.cursor = "pointer";
    thName.addEventListener("click", () => sortTable(table, 0));
    
    const thCategory = document.createElement("th");
    thCategory.textContent = "Category";
    thCategory.style.border = "1px solid #ddd";
    thCategory.style.padding = "10px";
    thCategory.style.backgroundColor = "#f5f5f5";
    thCategory.style.textAlign = "left";
    thCategory.style.cursor = "pointer";
    thCategory.addEventListener("click", () => sortTable(table, 1));
    
    headerRow.appendChild(thName);
    headerRow.appendChild(thCategory);
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Table content
    const tbody = document.createElement("tbody");
    allClasses.forEach((classItem, index) => {
        const row = document.createElement("tr");
        
        // Zebra stripes
        if (index % 2 === 0) {
            row.style.backgroundColor = "#f9f9f9";
        }
        
        const tdName = document.createElement("td");
        tdName.textContent = classItem.name;
        tdName.style.border = "1px solid #ddd";
        tdName.style.padding = "8px";
        
        const tdCategory = document.createElement("td");
        tdCategory.textContent = classItem.category;
        tdCategory.style.border = "1px solid #ddd";
        tdCategory.style.padding = "8px";
        
        // Specific colors
        const categoryColors = {
            'Aggregate Root': '#3498db',
            'Entity': '#e74c3c',
            'Factory': '#1abc9c',
            'Repository': '#95a5a6',
            'Service': '#2ecc71',
            'Value Object': '#f39c12',
            'Interface': '#f1c40f',
            'Exception': '#9b59b6',
            'Uncategorized': '#7f8c8d',
            'Forbidden': '#c0392b'
        };
        
        if (categoryColors[classItem.category]) {
            tdCategory.style.color = categoryColors[classItem.category];
            tdCategory.style.fontWeight = "600";
        }

        row.appendChild(tdName);
        row.appendChild(tdCategory);
        tbody.appendChild(row);
    });
    
    table.appendChild(tbody);
    container.appendChild(table);

    // Summary count
    const summary = document.createElement("p");
    summary.textContent = `Total: ${allClasses.length} classes`;
    summary.style.textAlign = "center";
    summary.style.marginTop = "10px";
    summary.style.fontWeight = "600";
    container.appendChild(summary);
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    const sortedRows = rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.toLowerCase();
        const bText = b.children[columnIndex].textContent.toLowerCase();
        return aText.localeCompare(bText);
    });

    // Refresh
    sortedRows.forEach((row, index) => {
        if (index % 2 === 0) {
            row.style.backgroundColor = "#f9f9f9";
        } else {
            row.style.backgroundColor = "white";
        }
        tbody.appendChild(row);
    });
}