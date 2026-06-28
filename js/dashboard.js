document.addEventListener("DOMContentLoaded", function () {

    const navItems = document.querySelectorAll(".nav-item[data-section]");
    const mainContent = document.getElementById("mainContent");
    const role = window.userRole || "member"; 
    let currentActiveSection = "home"; 
    
    let activeReceiverId = 0; 
    let chatInterval = null;

    function loadSection(section) {
        currentActiveSection = section;
        
        if (section !== "messages" && chatInterval) {
            clearInterval(chatInterval);
            chatInterval = null;
        }

        const openModalBtn = document.getElementById("openModalBtn");
        if (openModalBtn) {
            if (role === 'manager' && (section === "tasks" || section === "members")) {
                openModalBtn.style.display = "flex";
            } else {
                openModalBtn.style.display = "none";
            }
        }

        fetch(`api/${section}.php`)
            .then(res => res.json())
            .then(data => render(section, data))
            .catch(err => {
                console.error("Error loading section:", err);
                if (mainContent) {
                    mainContent.innerHTML = `<div style="text-align:center; padding:50px; color:#e53e3e;">خطا در دریافت اطلاعات از سرور.</div>`;
                }
            });
    }

    function render(section, data) {
        let html = "";

        if (section === "home") {
            let avatarHtml = "";
            if (Array.isArray(data.members_avatars)) {
                data.members_avatars.forEach(m => {
                    let avatarPath = m.avatar && m.avatar.trim() !== "" ? "image/" + m.avatar : "image/user.png";
                    if (m.role === 'manager' || m.role === 'owner' || m.avatar === 'manager.png') {
                        avatarPath = "image/manager.png";
                    }
                    avatarHtml += `
                        <div class="home-avatar-item" title="${m.full_name} (${m.username})">
                            <img src="${avatarPath}" alt="${m.full_name}" onerror="this.src='image/user.png'">
                        </div>
                    `;
                });
            }

            html = `
                <div class="content-card">
                    <h2>داشبورد پروژه</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div><h3>تسک‌های فعال</h3><div class="number">${data.active_tasks || 0}</div></div>
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-card">
                            <div><h3>کل اعضا</h3><div class="number">${data.members || 0}</div></div>
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-card">
                            <div><h3>انجام شده</h3><div class="number">${data.done_tasks || 0}</div></div>
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="home-members-section" style="margin-top: 25px; padding: 15px; background: #f8fafc; border-radius: 16px;">
                        <h4 style="margin-bottom: 12px; color: #4a5568; font-size: 0.95rem;"><i class="fas fa-users-viewfinder" style="margin-left: 6px; color:#764ba2;"></i> اعضای فعال پروژه</h4>
                        <div class="home-avatars-flex" style="display: flex; flex-wrap: wrap; gap: 10px;">
                            ${avatarHtml || '<p style="font-size:0.85rem; color:#a0aec0;">عضوی یافت نشد.</p>'}
                        </div>
                    </div>
                    <div class="charts-row" style="display: flex; gap: 20px; margin-top: 25px; flex-wrap: wrap;">
                        <div class="chart-container-box" style="flex: 1; min-width: 280px; background: #fff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <h4 style="text-align: center; margin-bottom: 15px; color:#2d3748;">وضعیت کنونی کارها</h4>
                            <div style="position: relative; height: 240px; display: flex; justify-content: center;">
                                <canvas id="taskPieChart"></canvas>
                            </div>
                        </div>
                        <div class="chart-container-box" style="flex: 1.5; min-width: 320px; background: #fff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <h4 style="text-align: center; margin-bottom: 15px; color:#2d3748;">کارهای انجام شده توسط هر کاربر</h4>
                            <div style="position: relative; height: 240px;">
                                <canvas id="userBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        if (section === "settings") {
    html = `
        <div class="content-card" style="max-width: 600px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px;"><i class="fas fa-cog"></i> تنظیمات کاربری</h2>
            <form id="settingsForm" class="settings-form">
                <label>نام و نام خانوادگی</label>
                <input type="text" id="setName" value="${data.user.full_name}" class="form-input">
                
                <label>ایمیل</label>
                <input type="email" id="setEmail" value="${data.user.email}" class="form-input">
                
                <label>رمز عبور جدید</label>
                <input type="password" id="setPass" placeholder="برای عدم تغییر خالی بگذارید" class="form-input">
                
                <button type="submit" class="btn-save" style="margin-top: 15px;">ذخیره تغییرات</button>
            </form>
            
            <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e2e8f0;">
            
            <h3>پروژه‌های شما</h3>
            <div class="projects-list">
                ${data.projects.map(p => `
                    <div class="project-item ${p.id == data.current_project_id ? 'active' : ''}">
                        <span>${p.name}</span>
                        ${p.id == data.current_project_id ? 
                            '<span class="badge-active">فعال</span>' : 
                            `<a href="switch_project.php?id=${p.id}" class="btn-switch">سوییچ</a>`}
                    </div>
                `).join('')}
            </div>
            <a href="create_project.php" class="btn-create-project">+ ایجاد پروژه جدید</a>
        </div>
    `;
}
        if (section === "tasks") {
            let todo = "", doing = "", done = "";
            if (Array.isArray(data)) {
                data.forEach(t => {
                    let taskUser = t.user_username || t.user || "";
                    if (role !== "manager" && taskUser !== window.currentUsername) {
                        return;
                    }

                    let priorityText = "متوسط";
                    if (t.priority === "high") priorityText = "زیاد";
                    if (t.priority === "low") priorityText = "کم";

                    let editButtonHtml = "";
                    if (role === 'manager') {
                        editButtonHtml = `
                            <button class="task-edit-btn" title="ویرایش تسک" data-id="${t.id}" data-title="${t.title}" data-desc="${t.description || ''}" data-user="${taskUser}" data-date="${t.due_date || ''}" data-priority="${t.priority || 'medium'}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        `;
                    }

                    const item = `
                        <div class="task-item" data-id="${t.id || 0}" draggable="true">
                            ${editButtonHtml}
                            <div style="font-weight: bold; color: #2d3748;">${t.title}</div>
                            ${t.description ? `<div style="font-size: 0.8rem; color: #4a5568; margin-top: 4px;">${t.description}</div>` : ''}
                            <div style="margin-top: 8px; font-size: 0.85rem; color: #718096; display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="fas fa-user" style="font-size: 0.75rem;"></i> مجری: ${t.user || taskUser}</span>
                                <span style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; background: #edf2f7;">اولویت: ${priorityText}</span>
                            </div>
                            ${t.due_date ? `<div style="margin-top: 5px;"><small style="color: #e53e3e;"><i class="far fa-clock"></i> پایان: ${t.due_date}</small></div>` : ''}
                        </div>
                    `;
                    if (t.status === "todo") todo += item;
                    if (t.status === "doing") doing += item;
                    if (t.status === "done") done += item;
                });
            }

            html = `
                <div class="tasks-board">
                    <div class="task-column" data-status="todo">
                        <h3><i class="far fa-circle" style="color: #cbd5e0;"></i> در انتظار</h3>
                        <div class="column-body" style="min-height: 350px; margin-top:20px;">${todo}</div>
                    </div>
                    <div class="task-column" data-status="doing">
                        <h3><i class="fas fa-spinner fa-spin" style="color: #dd6b20;"></i> در حال انجام</h3>
                        <div class="column-body" style="min-height: 350px; margin-top:20px;">${doing}</div>
                    </div>
                    <div class="task-column" data-status="done">
                        <h3><i class="fas fa-check-circle" style="color: #38a169;"></i> انجام شده</h3>
                        <div class="column-body" style="min-height: 350px; margin-top:20px;">${done}</div>
                    </div>
                </div>
            `;
        }

        if (section === "members") {
            let membersList = "";
            if (Array.isArray(data)) {
                data.forEach(m => {
                    let avatarPath = m.avatar && m.avatar.trim() !== "" ? "image/" + m.avatar : "image/user.png";
                    let roleText = 'عضو تیم';
                    let roleClass = 'role-member';
                    if (m.role === 'owner') { 
                        roleText = 'مالک پروژه'; 
                        roleClass = 'role-owner'; 
                        avatarPath = "image/manager.png";
                    } else if (m.role === 'manager') { 
                        roleText = 'مدیر پروژه'; 
                        roleClass = 'role-manager'; 
                        avatarPath = "image/manager.png";
                    }
                    let positionText = m.position ? m.position : "بدون پوزیشن مشخص";

                    membersList += `
                        <div class="member-card" data-username="${m.username}" data-name="${m.name}" data-position="${m.position || ''}" data-role="${m.role}">
                            <img src="${avatarPath}" class="member-avatar" alt="Avatar" onerror="this.src='image/user.png'">
                            <div class="member-name">
                                ${m.name}
                                <span style="font-size: 0.8rem; color: #a0aec0; font-weight: normal; display: block; margin-top: 2px;">@${m.username}</span>
                            </div>
                            <div class="member-position" style="margin-top: 8px;"><i class="fas fa-briefcase" style="font-size:0.75rem; margin-left:3px;"></i> ${positionText}</div>
                            <div class="member-badge-role ${roleClass}">${roleText}</div>
                        </div>
                    `;
                });
            }
            html = `
                <div class="content-card" style="background: transparent; box-shadow: none; padding: 0;">
                    <h2 style="margin-bottom: 25px; padding-right: 10px; color:#2d3748;">لیست اعضای پروژه</h2>
                    <div class="members-grid">
                        ${membersList || '<p style="grid-column:1/-1; text-align:center; color:#718096;">هنوز هیچ عضوی به این پروژه اضافه نشده است.</p>'}
                    </div>
                </div>
            `;
        }
        
        if (section === "messages") {
            html = `
                <div class="chat-container">
                    <div class="chat-sidebar">
                        <div class="chat-sidebar-header">گفتگوهای پروژه</div>
                        <div class="chat-user-list" id="chatUserList">
                            <div class="chat-user-item ${activeReceiverId === 0 ? 'active' : ''}" data-id="0">
                                <img src="image/fav.png" onerror="this.src='image/user.png'">
                                <div class="chat-user-info">
                                    <span class="chat-user-name">چت عمومی پروژه</span>
                                    <span class="chat-user-role">همه اعضا</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-main">
                        <div class="chat-header" id="chatTargetName">چت عمومی پروژه</div>
                        <div class="chat-messages" id="chatMessagesBox"></div>
                        <div class="chat-footer">
                            <input type="text" class="chat-input" id="chatInputMessage" placeholder="پیام خود را بنویسید...">
                            <button class="chat-send-btn" id="chatSendBtn"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            `;
        }

        if (mainContent) {
            mainContent.innerHTML = html;
        }

        if (section === "home") { initHomeCharts(data); }
        if (section === "tasks") { initDragAndDrop(); initTaskEditClicks(); }
        if (section === "members" && role === 'manager') { initMemberCardClicks(); }
        if (section === "messages") { initChatSystem(data); }
    }

    function initTaskEditClicks() {
        const editBtns = document.querySelectorAll(".task-edit-btn");
        const editTaskModal = document.getElementById("editTaskModal");
        
        editBtns.forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.stopPropagation(); 
                e.preventDefault();

                document.getElementById("editTaskId").value = this.dataset.id;
                document.getElementById("editTaskTitle").value = this.dataset.title;
                document.getElementById("editTaskDescription").value = this.dataset.desc;
                document.getElementById("editTaskAssignee").value = this.dataset.user;
                document.getElementById("editTaskDueDate").value = this.dataset.date;
                document.getElementById("editTaskPriority").value = this.dataset.priority;

                document.getElementById("edit-task-js-error").style.display = "none";
                if(editTaskModal) editTaskModal.style.display = "flex";
            });
        });
    }

    function initChatSystem(initialData) {
        const userListContainer = document.getElementById("chatUserList");
        const messagesBox = document.getElementById("chatMessagesBox");
        const sendBtn = document.getElementById("chatSendBtn");
        const inputMsg = document.getElementById("chatInputMessage");

        if (!userListContainer || !messagesBox) return;

        const userRolesMap = {};
        const userAvatarsMap = {};

        if (initialData && Array.isArray(initialData.sidebar_users)) {
            initialData.sidebar_users.forEach(user => {
                let userAvatar = user.avatar ? "image/" + user.avatar : "image/user.png";
                
                if (user.role === 'manager' || user.role === 'owner' || user.avatar === 'manager.png') {
                    userAvatar = "image/manager.png";
                }
                
                if (user.full_name) {
                    userRolesMap[user.full_name] = user.role;
                    userAvatarsMap[user.full_name] = user.avatar;
                }

                const userRoleText = user.role === 'manager' ? 'مدیر / مالک' : 'عضو تیم';
                const div = document.createElement("div");
                div.className = `chat-user-item ${activeReceiverId === parseInt(user.id) ? 'active' : ''}`;
                div.dataset.id = user.id;
                div.dataset.name = user.full_name;
                div.innerHTML = `
                    <img src="${userAvatar}" onerror="this.src='image/user.png'">
                    <div class="chat-user-info">
                        <span class="chat-user-name">${user.full_name}</span>
                        <span class="chat-user-role">${userRoleText}</span>
                    </div>
                `;
                userListContainer.appendChild(div);
            });
        }

        function fetchChatMessages() {
            fetch(`api/messages.php?fetch=1&receiver_id=${activeReceiverId}`)
                .then(res => res.json())
                .then(resData => {
                    if (resData.success && Array.isArray(resData.messages)) {
                        let msgsHtml = "";
                        resData.messages.forEach(msg => {
                            const sideClass = msg.is_me ? "me" : "other";
                            const senderRole = msg.role || userRolesMap[msg.sender_name] || "";
                            const senderAvatar = msg.avatar || userAvatarsMap[msg.sender_name] || "";

                            let msgAvatar = "image/user.png";
                            
                            if (
                                senderRole === 'manager' || 
                                senderRole === 'owner' || 
                                senderAvatar === 'manager.png' ||
                                (msg.is_me && (role === 'manager' || window.userRole === 'owner'))
                            ) {
                                msgAvatar = "image/manager.png";
                            } else if (senderAvatar && senderAvatar.trim() !== "") {
                                msgAvatar = senderAvatar.startsWith("image/") ? senderAvatar : "image/" + senderAvatar;
                            }

                            msgsHtml += `
                                <div class="chat-bubble ${sideClass}">
                                    <div class="chat-msg-wrapper">
                                        <img src="${msgAvatar}" onerror="this.src='image/user.png'">
                                        <div class="chat-text-box">${msg.message}</div>
                                    </div>
                                    <div class="chat-meta">
                                        <span>${msg.sender_name}</span> • <span>${msg.time}</span>
                                    </div>
                                </div>
                            `;
                        });
                        const currentScroll = messagesBox.scrollTop + messagesBox.clientHeight;
                        const totalHeight = messagesBox.scrollHeight;
                        
                        messagesBox.innerHTML = msgsHtml;
                        
                        if (totalHeight - currentScroll < 150 || currentScroll === 0) {
                            messagesBox.scrollTop = messagesBox.scrollHeight;
                        }
                    }
                }).catch(() => {});
        }

        fetchChatMessages();
        chatInterval = setInterval(fetchChatMessages, 3000);

        document.querySelectorAll(".chat-user-item").forEach(item => {
            item.addEventListener("click", function() {
                document.querySelectorAll(".chat-user-item").forEach(i => i.classList.remove("active"));
                this.classList.add("active");
                activeReceiverId = parseInt(this.dataset.id);
                document.getElementById("chatTargetName").textContent = activeReceiverId === 0 ? "چت عمومی پروژه" : this.dataset.name;
                messagesBox.innerHTML = '<div style="text-align:center; padding:20px; color:#94a3b8;">در حال بارگذاری پیام‌ها...</div>';
                fetchChatMessages();
            });
        });

        function sendMessageAction() {
            const text = inputMsg.value.trim();
            if (text === "") return;

            fetch("api/messages.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    message: text,
                    receiver_id: activeReceiverId
                })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    inputMsg.value = "";
                    fetchChatMessages();
                } else {
                    alert("خطا در ارسال پیام: " + result.message);
                }
            });
        }

        if (sendBtn && inputMsg) {
            sendBtn.addEventListener("click", sendMessageAction);
            inputMsg.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    sendMessageAction();
                }
            });
        }
    }

    function initHomeCharts(data) {
        const bk = data.task_breakdown || { todo: 0, doing: 0, done: 0 };
        const ctxPie = document.getElementById('taskPieChart');
        if (ctxPie) {
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['در انتظار', 'در حال انجام', 'انجام شده'],
                    datasets: [{
                        data: [bk.todo, bk.doing, bk.done],
                        backgroundColor: ['#d2b2f3', '#a36bdb', '#764ba2'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { font: { family: 'inherit' } } } }
                }
            });
        }

        const ctxBar = document.getElementById('userBarChart');
        if (ctxBar) {
            const userLabels = (data.users_chart && data.users_chart.labels) ? data.users_chart.labels : [];
            const userData = (data.users_chart && data.users_chart.data) ? data.users_chart.data : [];

            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: userLabels,
                    datasets: [{
                        label: 'تعداد تسک‌های موفق',
                        data: userData,
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return '#667eea'; 
                            const gradient = ctx.createLinearGradient(chartArea.left, chartArea.top, chartArea.right, chartArea.bottom);
                            gradient.addColorStop(0, '#667eea');
                            gradient.addColorStop(1, '#764ba2');
                            return gradient;
                        },
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { labels: { font: { family: 'inherit' } } } }
                }
            });
        }
    }

    function initDragAndDrop() {
        const draggables = document.querySelectorAll(".task-item");
        const columns = document.querySelectorAll(".task-column");

        draggables.forEach(draggable => {
            draggable.addEventListener("dragstart", () => draggable.classList.add("dragging"));
            draggable.addEventListener("dragend", () => draggable.classList.remove("dragging"));
        });

        columns.forEach(column => {
            column.addEventListener("dragover", (e) => {
                e.preventDefault();
                const draggingItem = document.querySelector(".dragging");
                if (draggingItem) {
                    column.querySelector(".column-body").appendChild(draggingItem);
                }
            });

            column.addEventListener("drop", () => {
                const draggingItem = document.querySelector(".dragging");
                if (draggingItem) {
                    fetch("api/update_task_status.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id: draggingItem.dataset.id, status: column.dataset.status })
                    })
                    .then(res => res.json())
                    .then(result => { if (!result.success) alert("خطا در به‌روزرسانی وضعیت تسک"); });
                }
            });
        });
    }

    function initMemberCardClicks() {
        const cards = document.querySelectorAll(".member-card");
        const editModal = document.getElementById("editMemberModal");
        
        cards.forEach(card => {
            card.addEventListener("click", function() {
                if (this.dataset.role === 'owner') {
                    alert("امکان ویرایش یا حذف مالک اصلی پروژه وجود ندارد.");
                    return;
                }
                document.getElementById("editMemberUsernameHidden").value = this.dataset.username;
                document.getElementById("editMemberFullName").value = this.dataset.name;
                document.getElementById("editMemberPosition").value = this.dataset.position;
                document.getElementById("editMemberRole").value = this.dataset.role;
                
                document.getElementById("edit-member-js-error").style.display = "none";
                if(editModal) editModal.style.display = "flex";
            });
        });
    }

    const openModalBtn = document.getElementById("openModalBtn");
    const taskModal = document.getElementById("taskModal");
    const editTaskModal = document.getElementById("editTaskModal");
    const memberModal = document.getElementById("memberModal");
    const editMemberModal = document.getElementById("editMemberModal");

    const taskJsError = document.getElementById("task-js-error");
    const memberJsError = document.getElementById("member-js-error");
    const memberUsernameStatus = document.getElementById("memberUsernameStatus");

    if (openModalBtn) {
        openModalBtn.addEventListener("click", function () {
            if (role !== 'manager') return;
            if (currentActiveSection === "tasks" && taskModal) {
                if(taskJsError) taskJsError.style.display = "none";
                taskModal.style.display = "flex";
            }
            if (currentActiveSection === "members" && memberModal) {
                if(memberJsError) memberJsError.style.display = "none";
                if(memberUsernameStatus) memberUsernameStatus.style.display = "none";
                memberModal.style.display = "flex";
            }
        });
    }

    if (document.getElementById("closeModalBtn")) {
        document.getElementById("closeModalBtn").addEventListener("click", () => { if(taskModal) taskModal.style.display = "none"; });
    }
    if (document.getElementById("closeEditTaskModalBtn")) {
        document.getElementById("closeEditTaskModalBtn").addEventListener("click", () => { if(editTaskModal) editTaskModal.style.display = "none"; });
    }
    if (document.getElementById("closeMemberModalBtn")) {
        document.getElementById("closeMemberModalBtn").addEventListener("click", () => { if(memberModal) memberModal.style.display = "none"; });
    }
    if (document.getElementById("closeEditMemberModalBtn")) {
        document.getElementById("closeEditMemberModalBtn").addEventListener("click", () => { if(editMemberModal) editMemberModal.style.display = "none"; });
    }

    window.addEventListener("click", function (e) {
        if (e.target === taskModal) taskModal.style.display = "none";
        if (e.target === editTaskModal) editTaskModal.style.display = "none";
        if (e.target === memberModal) memberModal.style.display = "none";
        if (e.target === editMemberModal) editMemberModal.style.display = "none";
    });

    const createTaskForm = document.getElementById("createTaskForm");
    if (createTaskForm && role === 'manager') {
        createTaskForm.addEventListener("submit", (e) => {
            e.preventDefault();
            if(taskJsError) taskJsError.style.display = "none";

            const title = document.getElementById("taskTitle").value.trim();
            const description = document.getElementById("taskDescription").value.trim();
            const assignee = document.getElementById("taskAssignee").value;
            const priority = document.getElementById("taskPriority").value;
            const dueDate = document.getElementById("taskDueDate").value;

            if (title === "" || assignee === "") {
                if(taskJsError) {
                    taskJsError.textContent = "لطفاً عنوان تسک و مسئول انجام را مشخص کنید.";
                    taskJsError.style.display = "block";
                }
                return;
            }

            fetch("api/create_task.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    title: title,
                    description: description,
                    user: assignee,
                    priority: priority,
                    due_date: dueDate
                })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) { 
                    taskModal.style.display = "none"; 
                    createTaskForm.reset(); 
                    loadSection("tasks"); 
                } else { 
                    if(taskJsError) {
                        taskJsError.textContent = "خطا: " + result.message;
                        taskJsError.style.display = "block";
                    }
                }
            })
            .catch(err => {
                if(taskJsError) {
                    taskJsError.textContent = "خطا در برقراری ارتباط با سرور.";
                    taskJsError.style.display = "block";
                }
            });
        });
    }

    const editTaskForm = document.getElementById("editTaskForm");
    if (editTaskForm && role === 'manager') {
        editTaskForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const editError = document.getElementById("edit-task-js-error");
            editError.style.display = "none";

            const taskData = {
                id: document.getElementById("editTaskId").value,
                title: document.getElementById("editTaskTitle").value.trim(),
                description: document.getElementById("editTaskDescription").value.trim(),
                user: document.getElementById("editTaskAssignee").value,
                due_date: document.getElementById("editTaskDueDate").value,
                priority: document.getElementById("editTaskPriority").value
            };

            if (taskData.title === "" || taskData.user === "") {
                editError.textContent = "لطفاً تمام فیلدهای اجباری را پر کنید.";
                editError.style.display = "block";
                return;
            }

            fetch("api/edit_task.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(taskData)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    editTaskModal.style.display = "none";
                    loadSection("tasks");
                } else {
                    editError.textContent = result.message;
                    editError.style.display = "block";
                }
            });
        });
    }

    const deleteTaskBtn = document.getElementById("deleteTaskBtn");
    if (deleteTaskBtn && role === 'manager') {
        deleteTaskBtn.addEventListener("click", function() {
            const taskId = document.getElementById("editTaskId").value;
            if (confirm("آیا از حذف کامل این تسک اطمینان دارید؟")) {
                fetch("api/delete_task.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id: taskId })
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        editTaskModal.style.display = "none";
                        loadSection("tasks");
                    } else {
                        alert(result.message);
                    }
                });
            }
        });
    }

    const memberUsernameInput = document.getElementById("memberUsername");
    let isUsernameValid = false;
    let typingTimer;

    if (memberUsernameInput && role === 'manager') {
        memberUsernameInput.addEventListener("input", function() {
            if (memberJsError) memberJsError.style.display = "none";
            clearTimeout(typingTimer);
            let uName = memberUsernameInput.value.trim();

            if (uName === "") {
                if (memberUsernameStatus) memberUsernameStatus.style.display = "none";
                isUsernameValid = false;
                return;
            }

            typingTimer = setTimeout(() => {
                fetch(`api/check_project_username.php?username=${encodeURIComponent(uName)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (memberUsernameStatus) {
                            memberUsernameStatus.style.display = "block";
                            if (data.status === 'available') {
                                memberUsernameStatus.style.color = "#38a169";
                                memberUsernameStatus.textContent = "✓ این نام کاربری برای پروژه آزاد است";
                                isUsernameValid = true;
                            } else {
                                memberUsernameStatus.style.color = "#e53e3e";
                                memberUsernameStatus.textContent = "✕ این نام کاربری قبلاً در این پروژه استفاده شده است";
                                isUsernameValid = false;
                            }
                        }
                    }).catch(() => { isUsernameValid = true; });
            }, 400);
        });
    }

    const memberPassInput = document.getElementById("memberPassword");
    const toggleMemberPass = document.getElementById("toggleMemberPass");
    const memberPasswordSuggest = document.getElementById("memberPasswordSuggest");
    const memberSuggestedPassStr = document.getElementById("memberSuggestedPass");
    const memberUseSuggestBtn = document.getElementById("memberUseSuggestBtn");

    if (toggleMemberPass && memberPassInput) {
        toggleMemberPass.addEventListener("click", () => {
            memberPassInput.type = memberPassInput.type === "password" ? "text" : "password";
        });
    }

    function generateSecurePassword() {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let password = "";
        for (let i = 0; i < 12; i++) { password += chars.charAt(Math.floor(Math.random() * chars.length)); }
        return password;
    }

    if (memberPassInput && role === 'manager') {
        memberPassInput.addEventListener("focus", () => {
            if(memberPassInput.value.length < 8) {
                const randomPass = generateSecurePassword();
                memberSuggestedPassStr.textContent = randomPass;
                memberPasswordSuggest.style.display = "flex";
            }
        });
        memberPassInput.addEventListener("input", () => {
            if (memberJsError) memberJsError.style.display = "none";
            if(memberPassInput.value.length >= 8) { memberPasswordSuggest.style.display = "none"; }
        });
    }

    if (memberUseSuggestBtn && role === 'manager') {
        memberUseSuggestBtn.addEventListener("click", () => {
            memberPassInput.type = "text"; 
            memberPassInput.value = memberSuggestedPassStr.textContent;
            memberPasswordSuggest.style.display = "none";
        });
    }

    const createMemberForm = document.getElementById("createMemberForm");
    if (createMemberForm && role === 'manager') {
        createMemberForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const fName = document.getElementById("memberFullName").value.trim();
            const uName = document.getElementById("memberUsername").value.trim();
            const emailValue = document.getElementById("memberEmail").value.trim();
            const posValue = document.getElementById("memberPosition").value.trim();
            const passValue = memberPassInput.value.trim();
            const roleValue = document.getElementById("memberRole").value;

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (fName === "" || uName === "" || emailValue === "" || passValue === "" || posValue === "") {
                if(memberJsError) { memberJsError.textContent = "لطفاً تمام فیلدها را پر کنید."; memberJsError.style.display = "block"; }
                return;
            }
            if (!emailRegex.test(emailValue)) {
                if(memberJsError) { memberJsError.textContent = "فرمت ایمیل وارد شده صحیح نیست."; memberJsError.style.display = "block"; }
                return;
            }
            if (passValue.length < 8) {
                if(memberJsError) { memberJsError.textContent = "رمز عبور نباید کمتر از ۸ کاراکتر باشد."; memberJsError.style.display = "block"; }
                return;
            }
            if (!isUsernameValid) {
                if(memberJsError) { memberJsError.textContent = "لطفاً یک نام کاربری غیرتکراری انتخاب کنید."; memberJsError.style.display = "block"; }
                return;
            }

            fetch("api/create_member.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ full_name: fName, username: uName, email: emailValue, position: posValue, password: passValue, role: roleValue })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) { 
                    memberModal.style.display = "none"; 
                    createMemberForm.reset();
                    loadSection("members");
                } else { 
                    if(memberJsError) { memberJsError.textContent = "خطا: " + result.message; memberJsError.style.display = "block"; }
                }
            });
        });
    }

    const editMemberForm = document.getElementById("editMemberForm");
    if (editMemberForm && role === 'manager') {
        editMemberForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const username = document.getElementById("editMemberUsernameHidden").value;
            const position = document.getElementById("editMemberPosition").value.trim();
            const roleValue = document.getElementById("editMemberRole").value;
            const editError = document.getElementById("edit-member-js-error");

            if (position === "") {
                editError.textContent = "فیلد پوزیشن نمی‌تواند خالی باشد.";
                editError.style.display = "block";
                return;
            }

            fetch("api/edit_member.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ username: username, position: position, role: roleValue })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) { editMemberModal.style.display = "none"; loadSection("members"); } 
                else { editError.textContent = result.message; editError.style.display = "block"; }
            });
        });
    }

    const deleteMemberBtn = document.getElementById("deleteMemberBtn");
    if (deleteMemberBtn && role === 'manager') {
        deleteMemberBtn.addEventListener("click", function() {
            const username = document.getElementById("editMemberUsernameHidden").value;
            if (confirm(`آیا از حذف این کاربر از پروژه اطمینان دارید؟`)) {
                fetch("api/delete_member.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ username: username })
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) { editMemberModal.style.display = "none"; loadSection("members"); } 
                    else { alert(result.message); }
                });
            }
        });
    }

    navItems.forEach(item => {
        item.addEventListener("click", function () {
            navItems.forEach(i => i.classList.remove("active"));
            this.classList.add("active");
            loadSection(this.dataset.section);
        });
    });

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            if (confirm("خروج؟")) { window.location.href = "logout.php"; }
        });
    }

    loadSection("home");
});

// تنظیمات در dashboard.js
$(document).ready(function() {
    $('#taskDueDate, #editTaskDueDate').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false, // غیرفعال برای جلوگیری از تداخل با مقدار دریافتی
        calendar:{
            persian: {
                locale: 'en'
            }
        }
    });
});