document.addEventListener("DOMContentLoaded", function () {
    // ==========================================
    // SIDEBAR LOGIC
    // ==========================================
    const sidebar = document.querySelector(".sidebar");
    const toggler = document.querySelector(".sidebar-toggler");
    const menuBtn = document.querySelector(".sidebar-menu-button");

    const toggleSidebar = () => {
        closeAllDropdowns();
        if (sidebar) sidebar.classList.toggle("collapsed");
    };

    if (toggler) toggler.addEventListener("click", toggleSidebar);
    if (menuBtn) menuBtn.addEventListener("click", toggleSidebar);
    
    if (window.innerWidth <= 1024 && sidebar) {
        sidebar.classList.add("collapsed");
    }

    // ==========================================
    // DROPDOWN LOGIC
    // ==========================================
    const toggleDropdown = (dropdown, menu, isOpen) => {
      dropdown.classList.toggle("open", isOpen);
      if (menu) menu.style.height = isOpen ? `${menu.scrollHeight}px` : 0;
    };

    const closeAllDropdowns = () => {
      document.querySelectorAll(".dropdown-container.open").forEach((openDropdown) => {
        toggleDropdown(openDropdown, openDropdown.querySelector(".dropdown-menu"), false);
      });
    };

    document.querySelectorAll(".dropdown-toggle").forEach((dropdownToggle) => {
      dropdownToggle.addEventListener("click", (e) => {
        e.preventDefault();
        const dropdown = dropdownToggle.closest(".dropdown-container");
        const menu = dropdown.querySelector(".dropdown-menu");
        const isOpen = dropdown.classList.contains("open");
        
        closeAllDropdowns();
        toggleDropdown(dropdown, menu, !isOpen);
      });
    });
});

// ==========================================================
// UNIFIED FILTER AND SEARCH EXECUTION
// ==========================================================
document.addEventListener("DOMContentLoaded", function () {
    
    // 1. EMPLOYEE PAGE FILTERING
    const empSearch = document.getElementById("employeeSearch");
    const empTabs = document.querySelectorAll(".filter-bar .filter-tab");
    const empRows = document.querySelectorAll(".data-table tbody tr");

    if (empRows.length > 0 && empSearch) {
        let activeFilter = "all";
        let searchQuery = "";

        function applyEmployeeFilter() {
            let visibleIndex = 0; // Track only visible rows

            empRows.forEach(row => {
                if (row.querySelector(".empty-cell")) return;
                const rowDept = (row.getAttribute("data-department") || "").toLowerCase().trim();
                const rowText = row.textContent.toLowerCase();

                const matchesTab = (activeFilter === "all" || rowDept === activeFilter);
                const matchesSearch = rowText.includes(searchQuery);

                if (matchesTab && matchesSearch) {
                    row.style.display = "";
                    
                    // Manually toggle striping color based on visible counter
                    if (visibleIndex % 2 === 1) {
                        row.style.backgroundColor = "#D9D9D9";
                    } else {
                        row.style.backgroundColor = ""; // Fallback to beige container default
                    }
                    visibleIndex++;
                } else {
                    row.style.display = "none";
                }
            });
        }

        empTabs.forEach(tab => {
            tab.addEventListener("click", function () {
                empTabs.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                activeFilter = (this.getAttribute("data-filter") || "all").toLowerCase().trim();
                applyEmployeeFilter();
            });
        });

        empSearch.addEventListener("input", function (e) {
            searchQuery = e.target.value.toLowerCase();
            applyEmployeeFilter();
        });
    }

    // 2. LEAVE REQUESTS PAGE FILTERING
    const leaveSearch = document.getElementById("leaveSearch");
    const leaveTabs = document.querySelectorAll(".filter-bar .filter-tab");
    const leaveRows = document.querySelectorAll(".data-table tbody tr");

    if (leaveRows.length > 0 && leaveSearch && !document.getElementById("employeeSearch")) {
        let activeFilter = "all";
        let searchQuery = "";

        function applyLeaveFilter() {
            let visibleIndex = 0; // Track only visible rows

            leaveRows.forEach(row => {
                if (row.querySelector(".empty-cell")) return;
                const rowStatus = (row.getAttribute("data-status") || "").toLowerCase().trim();
                const rowText = row.textContent.toLowerCase();

                const matchesTab = (activeFilter === "all" || rowStatus === activeFilter);
                const matchesSearch = rowText.includes(searchQuery);

                if (matchesTab && matchesSearch) {
                    row.style.display = "";
                    
                    // Manually toggle striping color based on visible counter
                    if (visibleIndex % 2 === 1) {
                        row.style.backgroundColor = "#D9D9D9";
                    } else {
                        row.style.backgroundColor = ""; // Fallback to beige container default
                    }
                    visibleIndex++;
                } else {
                    row.style.display = "none";
                }
            });
        }

        leaveTabs.forEach(tab => {
            tab.addEventListener("click", function () {
                leaveTabs.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                activeFilter = (this.getAttribute("data-filter") || "all").toLowerCase().trim();
                applyLeaveFilter();
            });
        });

        leaveSearch.addEventListener("input", function (e) {
            searchQuery = e.target.value.toLowerCase();
            applyLeaveFilter();
        });
    }

    // 3. ATTENDANCE PAGE FILTERING
    const attSearch = document.getElementById("attendanceSearch");
    const attTabs = document.querySelectorAll(".filter-bar .filter-tab");
    const attRows = document.querySelectorAll(".data-table tbody tr");

    if (attRows.length > 0 && attSearch) {
        let activeFilter = "all";
        let searchQuery = "";

        function applyAttendanceFilter() {
          let visibleIndex = 0; // Track only rows that pass both tab and search filters

          attRows.forEach(row => {
              if (row.querySelector(".empty-state")) return; // Skip layout exception elements
              
              // Read work type (wfo/wfh) or status pill text mapping
              const workPill = row.querySelector(".work-pill")?.textContent.toLowerCase().trim() || "";
              const statusPill = row.querySelector(".status-pill")?.textContent.toLowerCase().replace(" ", "-").trim() || "";
              const rowText = row.textContent.toLowerCase();

              let matchesTab = false;
              if (activeFilter === "all") {
                  matchesTab = true;
              } else if (activeFilter === "wfo" || activeFilter === "wfh") {
                  const mappedWork = workPill === "office" ? "wfo" : "wfh";
                  matchesTab = (mappedWork === activeFilter);
              } else {
                  matchesTab = (statusPill === activeFilter);
              }

              const matchesSearch = rowText.includes(searchQuery);
              
              // Execute simultaneous visibility filtering and row striping updates
              if (matchesTab && matchesSearch) {
                  row.style.display = ""; // Render the row visible
                  
                  // Apply alternating dark gray background color tint to the layout view
                  if (visibleIndex % 2 === 1) {
                      row.style.backgroundColor = "#D9D9D9";
                  } else {
                      row.style.backgroundColor = ""; // Reset to your default --beige container theme background
                  }
                  visibleIndex++; // Increment index counter exclusively for visible items
              } else {
                  row.style.display = "none"; // Hide rows failing validation metrics
              }
          });
        }

        attTabs.forEach(tab => {
            tab.addEventListener("click", function () {
                attTabs.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                activeFilter = (this.getAttribute("data-filter") || "all").toLowerCase().trim();
                applyAttendanceFilter();
            });
        });

        attSearch.addEventListener("input", function (e) {
            searchQuery = e.target.value.toLowerCase();
            applyAttendanceFilter();
        });
    }

    // ==========================================
    // 4. DYNAMIC TABLE SORTING (ALL TABLES)
    // ==========================================
    const sortHeaders = document.querySelectorAll(".data-table th");
    sortHeaders.forEach((header, index) => {
        const sortIcon = header.querySelector(".sort-icon");
        if (!sortIcon) return;

        let ascending = true;
        header.style.cursor = "pointer";
        header.addEventListener("click", () => {
            const table = header.closest("table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));
            if (rows.length === 1 && rows[0].querySelector("td[colspan]")) return;

            rows.sort((rowA, rowB) => {
                const cellA = rowA.children[index].textContent.trim();
                const cellB = rowB.children[index].textContent.trim();
                const isNum = !isNaN(cellA) && !isNaN(cellB);
                if (isNum) return ascending ? cellA - cellB : cellB - cellA;
                return ascending 
                    ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' })
                    : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
            });

            rows.forEach(row => tbody.appendChild(row));
            ascending = !ascending;
            sortIcon.textContent = ascending ? "▲" : "▼";
        });
    });
});