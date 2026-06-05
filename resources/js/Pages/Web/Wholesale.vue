<template>
  <div>
    <Head title="Mua sỉ - BigBag Premium Utility Carry Gear" />

    <!-- Header -->
    <header class="w-full top-0 sticky z-[100] glass-nav border-b border-outline-variant shadow-sm bg-background">
      <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-margin-desktop py-4">
        <div class="flex items-center gap-8">
          <Link class="font-headline-lg text-headline-md tracking-tighter text-primary" href="/">BigBag.vn</Link>
          <div class="hidden md:flex items-center gap-6">
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Balo</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Laptop</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Du Lịch</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Thời Trang</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Cao Cấp</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Thể Thao</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Đa Năng</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Công Nghệ</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Chống Trộm</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Đi Học</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Giáo Viên</Link>
                </div>
              </div>
            </div>
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Cặp - Túi</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp Xách</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Du Lịch</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Đeo Chéo</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp Túi Macbook</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi iPad</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Gym</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Thể Thao</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp Giáo Viên</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Sinh Viên</Link>
                </div>
              </div>
            </div>
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Brands</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Tomtoc</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Mark Ryden</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Rokin</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Sakos</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Mikkor</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Hier</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Bopai</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Bange</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Kmore</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Kingbag</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Simple Carry</Link>
                </div>
              </div>
            </div>
            <Link :href="route('wholesale')" class="font-label-md text-label-md text-primary border-b-2 border-primary pb-1">Mua sỉ</Link>
            <Link :href="route('promotion')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Khuyến mãi</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">Giới thiệu</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">New Arrivals</Link>
          </div>
        </div>
        <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
          <div class="relative w-full">
            <input class="w-full bg-surface-container border border-outline-variant rounded-full py-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" placeholder="Tìm kiếm sản phẩm..." type="text">
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">person</span>
          </button>
          <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">2</span>
          </Link>
        </div>
      </nav>
    </header>

    <main>
      <!-- Hero Section -->
      <section class="relative bg-surface-container-low overflow-hidden">
        <div class="max-w-container-max-width mx-auto px-6 md:px-margin-desktop py-section-gap grid grid-cols-1 lg:grid-cols-2 gap-gutter items-center">
          <div class="z-10">
            <span class="bg-primary/10 text-primary font-label-lg text-label-lg px-4 py-1 rounded-full mb-stack-md inline-block">B2B & WHOLESALE</span>
            <h1 class="font-display-lg text-on-surface leading-tight lg:text-6xl mb-10">Giải Pháp Balo Doanh Nghiệp Toàn Diện</h1>
            <p class="font-body-lg text-secondary max-w-xl text-xl mb-12">BigBag cung cấp dịch vụ sản xuất và cung ứng balo số lượng lớn với tiêu chuẩn cao cấp, thiết kế tối ưu hóa cho nhận diện thương hiệu và quà tặng doanh nghiệp. Chúng tôi cam kết chất lượng bền bỉ, tiến độ nhanh chóng và giải pháp may đo riêng biệt cho từng đối tác.</p>
            <div class="flex gap-stack-md justify-center lg:justify-start">
              <a class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label-lg text-label-lg hover:shadow-lg transition-all flex items-center gap-2 uppercase tracking-wide" href="#contact">
                NHẬN BÁO GIÁ NGAY
                <span class="material-symbols-outlined">arrow_forward</span>
              </a>
            </div>
          </div>
          <div class="relative">
            <img alt="Corporate backpacks" class="rounded-xl shadow-2xl w-full h-[500px] object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAPOEllp3eC6aMxqqXyMehfbhNDerSNmtfj3mZhrjOnXP623TDYL8i26XGXe5IHdKHyh6MSnOeYFdMNcx4jUjR6Vu6OX17G3f7FeYXKawZConl3d-PTsLdlf35jOIGCSDoHgk_hKzs6uCxwhdIcuSeRkPbLXdV1VcOIjVPgproadEWitavl6uh7PmPjOh72gxXswaBAt6cMugz9Kue06bvlJbTmIsKfj_Av_omrfNJXndz4xEzjGUjE8Jg1nGbwciTPfishwijGWvtS">
          </div>
        </div>
      </section>

      <!-- Services Bento Grid -->
      <section class="max-w-container-max-width mx-auto px-6 md:px-margin-desktop py-section-gap">
        <div class="text-center mb-stack-lg">
          <h2 class="font-headline-lg text-headline-lg mb-stack-sm border-b-4 border-primary inline-block pb-2">Dịch Vụ Cung Cấp Sỉ</h2>
          <p class="font-body-md text-body-md text-secondary mt-4">Tối ưu hóa ngân sách và nâng tầm thương hiệu của bạn</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 auto-rows-[240px]">
          <div class="md:col-span-2 md:row-span-1 bg-white p-8 border border-gray-warm rounded-xl flex flex-col justify-between hover:shadow-md transition-shadow">
            <div><span class="material-symbols-outlined text-primary mb-4 text-3xl">branding_watermark</span><h3 class="font-headline-sm text-headline-sm mb-2">Gia Công Logo Thương Hiệu</h3><p class="font-body-sm text-body-sm text-secondary">In ấn, thêu hoặc dập nổi logo doanh nghiệp trên các dòng sản phẩm cao cấp, đảm bảo độ sắc nét và bền bỉ theo thời gian.</p></div>
          </div>
          <div class="bg-surface-container p-8 rounded-xl flex flex-col justify-center hover:shadow-md transition-shadow">
            <span class="material-symbols-outlined text-primary mb-4 text-3xl">design_services</span><h3 class="font-headline-sm text-headline-sm mb-2">Thiết Kế Riêng</h3><p class="font-body-sm text-body-sm text-secondary">Tùy chỉnh ngăn chứa và tính năng theo yêu cầu sử dụng đặc thù của nhân viên hoặc khách hàng.</p>
          </div>
          <div class="bg-charcoal-deep text-white p-8 rounded-xl flex flex-col justify-end hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary/20 rounded-full"></div>
            <span class="material-symbols-outlined text-primary mb-4 text-3xl">payments</span><h3 class="font-headline-sm text-headline-sm mb-2">Chiết Khấu Hấp Dẫn</h3><p class="font-body-sm text-body-sm text-white/70">Càng mua nhiều, ưu đãi càng lớn. Chính sách giá sỉ cạnh tranh nhất thị trường cho đơn hàng từ 50 sản phẩm.</p>
          </div>
          <div class="md:col-span-2 bg-white p-8 border border-gray-warm rounded-xl flex items-center gap-8 hover:shadow-md transition-shadow">
            <div class="w-1/3"><img alt="Logistics" class="rounded-lg object-cover h-32 w-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDR-dehP2vvVAjMINvlzoTppIxiqc9mK7SmW-PJ2xWTI1o7xLSx15p1Z8wLO-9dUn8uXPmJ_2qgcC5h42t85QK56sdURs3PUi6HZ6qjsxRLUsgbCh2Z3hT06fwFKzrLms0n7Lb6aMpKW9DolcS-nEwfc7U6JMIONHN2WSUxO84Kq36Pqc_9QkNEM1jNqSZQ1nvQHvZkIzHU7wwRGJwBMMviJqp-4LrdbO8N4pf83X5Zi244dHJpsPL-_s10hpRTPHatrv9YceZVfpQi"/></div>
            <div class="w-2/3"><h3 class="font-headline-sm text-headline-sm mb-2">Giao Hàng Toàn Quốc</h3><p class="font-body-sm text-body-sm text-secondary">Hỗ trợ vận chuyển tận nơi trong thời gian ngắn nhất, đảm bảo quy trình đóng gói tiêu chuẩn cao cấp.</p></div>
          </div>
        </div>
      </section>

      <!-- Product Gallery -->
      <section class="bg-surface-container-low py-section-gap">
        <div class="max-w-container-max-width mx-auto px-6 md:px-margin-desktop">
          <div class="flex flex-col md:flex-row justify-between items-end mb-stack-lg gap-4">
            <div><h2 class="font-headline-lg text-headline-lg">Mẫu Balo Tiêu Biểu</h2><p class="font-body-md text-body-md text-secondary">Các dòng sản phẩm được doanh nghiệp ưa chuộng nhất 2024</p></div>
            <button class="text-primary font-label-lg text-label-lg flex items-center gap-1 hover:underline uppercase tracking-wider">XEM TẤT CẢ MẪU<span class="material-symbols-outlined">chevron_right</span></button>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg p-stack-md group cursor-pointer border border-transparent hover:border-primary/20 hover:shadow-xl transition-all">
              <div class="aspect-[3/4] overflow-hidden rounded-md mb-4 bg-gray-50"><img alt="Balo Elite" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB2moOzz6ydHoJfraJitRWBuhER7pbb6LXfkQIb0qAqGtaP1XIqseW0VZsekc10A4VzG9yfRQf5H5WgSBahClC4oNshtyBBXXpRYCsIeE1ZPwLAoGM13Iu_NvqijHnL8uQ7nCIPsrkKmid7qIGL7BrC3D9BHPlwY9e5xz4Z0fGThAjp_IqneXLLXvtV0V2p9zmMtsDhpE6FMBTy8cyLTr1zQ4EEPRNGEP4rg3DcwDUM5X0eoUpCEKa5LrE3dTEMJ0JaEboY82dQDGER"/></div>
              <h4 class="font-label-lg text-label-lg mb-1">Balo Doanh Nhân "Elite"</h4><p class="text-secondary text-body-sm mb-4">Mẫu quà tặng cao cấp nhất</p><div class="flex items-center gap-2"><span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-label-md font-label-md">Best Seller</span></div>
            </div>
            <div class="bg-white rounded-lg p-stack-md group cursor-pointer border border-transparent hover:border-primary/20 hover:shadow-xl transition-all">
              <div class="aspect-[3/4] overflow-hidden rounded-md mb-4 bg-gray-50"><img alt="Balo Commuter" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIIGnYF1bk7O3WW8_rRR7HTixjVlOIm21_O5gIkElDeZKANejYSlLRzVv5TN0HmbeZMxv3fNQRTnn3ZxTSxg8La_G5F7wO_ayA1wY2xCmzXvdbZTigUlL8df-JW68zWYcu-uzT80fNo6XbssHyv7NGYTnMB746ubigSN24VA5d0UsNiPxx1WBvb46BdfVHMrkzKArcyePMpSKLEwZvRAbO14_OiDbLI6nHNDzpPTr_zctRXqBVLityHCxYlIz67RxCmKsAB9biQGtd"/></div>
              <h4 class="font-label-lg text-label-lg mb-1">Balo Công Sở "Commuter"</h4><p class="text-secondary text-body-sm mb-4">Tối ưu cho laptop 15.6 inch</p><div class="flex items-center gap-2"><span class="bg-surface-container text-secondary px-2 py-0.5 rounded text-label-md font-label-md">Đa năng</span></div>
            </div>
            <div class="bg-white rounded-lg p-stack-md group cursor-pointer border border-transparent hover:border-primary/20 hover:shadow-xl transition-all">
              <div class="aspect-[3/4] overflow-hidden rounded-md mb-4 bg-gray-50"><img alt="Balo Tech" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBWw0G7zsHP75ACx9IlLRIhUPTiFkgGORQiFhhDgoXa09BMwQG3X1whWr5Oj9NKjEmR9kd-df43FnYQEfJdGeVb4X7VIqCgCzw1G3ikd4dwYvhb7Mkb4RQOwkcBufnmi_GTUWoWSiypSTFn2aY0Sfn1wWprjNTGYxLy-8OZx3eHtQiJvR3Qdr73ws0K338b70C1s4A827U1wV3RRv9AM5IS-OF0sqAEzRTWIobGtECTyqIFA8vbtZywXJGmNJrZdoq9jCbjoV2OoamS"/></div>
              <h4 class="font-label-lg text-label-lg mb-1">Balo Tech "Nova"</h4><p class="text-secondary text-body-sm mb-4">Thiết kế hiện đại, chống trộm</p><div class="flex items-center gap-2"><span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-label-md font-label-md">New Tech</span></div>
            </div>
            <div class="bg-white rounded-lg p-stack-md group cursor-pointer border border-transparent hover:border-primary/20 hover:shadow-xl transition-all">
              <div class="aspect-[3/4] overflow-hidden rounded-md mb-4 bg-gray-50"><img alt="Balo Nomad" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDLPnuw842DL1jTFiGRfKjFnJgXSvR8yTw3Cln1fCp4pGDTHWYCsQvF0tp8WoRrHOZCb3zW32KXqShreOEPEA-0o0NkTvNnp4NbQC--zjsc4DDK3STOFH8PN7_xEHzSJ3wqzB5KfJR8WD_eo_V3RRmwPNQLQpsDEHl-m41AKinNlruJFAUuPu5rWXgrjhyPesqtU0GmLECTQ2GOO7gBMYn89pvopxOB9cB82Z3yi7LTPiHicIft9trIGs_giSsQUuKUsL0rpeWz1KQF"/></div>
              <h4 class="font-label-lg text-label-lg mb-1">Balo Du Lịch "Nomad"</h4><p class="text-secondary text-body-sm mb-4">Sức chứa lớn cho chuyến công tác</p><div class="flex items-center gap-2"><span class="bg-surface-container text-secondary px-2 py-0.5 rounded text-label-md font-label-md">Tiện dụng</span></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Benefits Section -->
      <section class="max-w-container-max-width mx-auto px-6 md:px-margin-desktop py-section-gap">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-stack-lg items-center">
          <div class="order-2 lg:order-1 grid grid-cols-2 gap-4">
            <div class="bg-white border border-gray-warm p-6 rounded-lg text-center hover:border-primary/30 transition-colors"><span class="material-symbols-outlined text-primary text-4xl mb-2">verified</span><h5 class="font-headline-sm text-headline-sm mb-1">Chất Lượng</h5><p class="text-body-sm text-secondary">Kiểm định nghiêm ngặt</p></div>
            <div class="bg-white border border-gray-warm p-6 rounded-lg text-center mt-8 hover:border-primary/30 transition-colors"><span class="material-symbols-outlined text-primary text-4xl mb-2">schedule</span><h5 class="font-headline-sm text-headline-sm mb-1">Đúng Hạn</h5><p class="text-body-sm text-secondary">Đảm bảo tiến độ bàn giao</p></div>
            <div class="bg-white border border-gray-warm p-6 rounded-lg text-center hover:border-primary/30 transition-colors"><span class="material-symbols-outlined text-primary text-4xl mb-2">support_agent</span><h5 class="font-headline-sm text-headline-sm mb-1">Tận Tâm</h5><p class="text-body-sm text-secondary">Hỗ trợ 24/7 chuyên nghiệp</p></div>
            <div class="bg-white border border-gray-warm p-6 rounded-lg text-center mt-8 hover:border-primary/30 transition-colors"><span class="material-symbols-outlined text-primary text-4xl mb-2">history_edu</span><h5 class="font-headline-sm text-headline-sm mb-1">Hợp Đồng</h5><p class="text-body-sm text-secondary">Minh bạch & rõ ràng</p></div>
          </div>
          <div class="order-1 lg:order-2 pl-0 lg:pl-12">
            <h2 class="font-headline-lg text-headline-lg mb-stack-md border-l-4 pl-4 border-primary">Tại Sao Chọn BigBag Cho Doanh Nghiệp?</h2>
            <p class="font-body-md text-body-md text-secondary mb-stack-md leading-relaxed">Với hơn 10 năm kinh nghiệm trong lĩnh vực sản xuất và phân phối túi xách, BigBag thấu hiểu giá trị của từng sản phẩm khi đến tay nhân viên và khách hàng của bạn. Chúng tôi không chỉ bán balo, chúng tôi cung cấp giải pháp gia tăng giá trị thương hiệu.</p>
            <ul class="space-y-4"><li class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="font-body-md text-on-surface">Mẫu mã đa dạng, dẫn đầu xu hướng thời trang công sở.</span></li><li class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="font-body-md text-on-surface">Vật liệu cao cấp, độ bền tối thiểu 3-5 năm.</span></li><li class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="font-body-md text-on-surface">Quy trình làm việc tinh gọn, tiết kiệm thời gian.</span></li></ul>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="max-w-container-max-width mx-auto px-6 md:px-margin-desktop py-section-gap" id="contact">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 border border-gray-warm">
          <div class="bg-primary p-12 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="z-10">
              <h2 class="font-headline-lg text-headline-lg mb-stack-md">Bắt Đầu Dự Án Doanh Nghiệp</h2>
              <p class="font-body-lg text-body-lg text-white/80 mb-stack-lg">Đội ngũ chuyên viên tư vấn của BigBag sẵn sàng hỗ trợ bạn thiết kế và báo giá chi tiết trong vòng 30 phút.</p>
              <div class="space-y-6">
                <div class="flex items-center gap-4"><span class="material-symbols-outlined bg-white/20 p-2 rounded-full">call</span><div><p class="text-label-md opacity-80">Hotline</p><p class="font-bold">1900 1234</p></div></div>
                <div class="flex items-center gap-4"><span class="material-symbols-outlined bg-white/20 p-2 rounded-full">mail</span><div><p class="text-label-md opacity-80">Email</p><p class="font-bold">b2b@bigbag.vn</p></div></div>
              </div>
            </div>
          </div>
          <div class="p-12 bg-surface-container-lowest">
            <h3 class="font-headline-sm text-headline-sm mb-6">Yêu Cầu Báo Giá (B2B)</h3>
            <form class="space-y-4" @submit.prevent="submitRequest">
              <div><label class="block text-label-md font-label-md mb-1 text-secondary">Tên công ty</label><input class="w-full rounded-lg border-outline-variant focus:ring-primary focus:border-primary" placeholder="Nhập tên doanh nghiệp của bạn" type="text" v-model="form.company"></div>
              <div class="grid grid-cols-2 gap-4"><div><label class="block text-label-md font-label-md mb-1 text-secondary">Email</label><input class="w-full rounded-lg border-outline-variant focus:ring-primary focus:border-primary" placeholder="email@congty.com" type="email" v-model="form.email"></div><div><label class="block text-label-md font-label-md mb-1 text-secondary">Số điện thoại</label><input class="w-full rounded-lg border-outline-variant focus:ring-primary focus:border-primary" placeholder="09xx xxx xxx" type="tel" v-model="form.phone"></div></div>
              <div><label class="block text-label-md font-label-md mb-1 text-secondary">Tổng số lượng dự kiến</label><input class="w-full rounded-lg border-outline-variant focus:ring-primary focus:border-primary" placeholder="Ví dụ: 100" type="number" v-model="form.quantity"></div>
              <div><label class="block text-label-md font-label-md mb-1 text-secondary">Yêu cầu cụ thể</label><textarea class="w-full rounded-lg border-outline-variant focus:ring-primary focus:border-primary" placeholder="Chi tiết về sản phẩm, in ấn logo, thời gian nhận hàng..." rows="4" v-model="form.requirements"></textarea></div>
              <button class="w-full bg-primary text-on-primary py-4 rounded-lg font-headline-sm text-headline-sm hover:shadow-lg transition-all mt-4 uppercase tracking-widest font-bold" type="submit">GỬI YÊU CẦU BÁO GIÁ</button>
              <p class="text-center text-label-md text-secondary mt-2 italic">Chúng tôi sẽ phản hồi trong vòng 30 phút</p>
            </form>
          </div>
        </div>
      </section>
    </main>

    <!-- Floating Chatbot -->
    <button class="fixed bottom-6 right-6 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-white z-50 hover:scale-110 transition-transform bg-primary"><span class="material-symbols-outlined text-3xl">chat</span></button>

    <!-- Footer -->
    <footer class="w-full border-t border-outline-variant font-['Montserrat'] py-16 text-[#333333]" style="background-color: #F3F3F3;">
      <div class="grid grid-cols-1 md:grid-cols-4 px-margin-desktop max-w-container-max-width mx-auto gap-12 items-start">
        <div class="space-y-4"><div class="font-headline-sm text-headline-sm font-bold text-[#333333]">BigBag.vn</div><p class="font-body-sm leading-relaxed text-[#333333]">© 2025 BigBag. Premium Utility Carry Gear.</p></div>
        <div class="flex flex-col gap-2"><h4 class="font-label-lg uppercase mb-2 text-[#333333]">VỀ BIGBAG</h4><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Về chúng tôi</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Phát triển bền vững</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Liên hệ</Link></div>
        <div class="flex flex-col gap-2"><h4 class="font-label-lg uppercase mb-2 text-[#333333]">HỖ TRỢ KHÁCH HÀNG</h4><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Giao hàng & Trả hàng</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo hành</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo mật</Link></div>
        <div class="flex flex-col gap-4"><h4 class="font-label-lg uppercase mb-2 text-[#333333]">ĐĂNG KÝ NHẬN TIN</h4><div class="flex gap-2"><input class="bg-white border border-[#333333] px-3 py-2 text-body-sm w-full focus:border-primary outline-none placeholder:text-[#333333]/50 rounded-none" placeholder="Email của bạn" type="email"><button class="bg-transparent border border-[#333333] text-[#333333] px-4 py-2 flex items-center justify-center hover:bg-primary/10 rounded-none transition-colors"><span class="material-symbols-outlined">arrow_forward</span></button></div><div class="flex gap-4"><span class="material-symbols-outlined hover:text-primary cursor-pointer">public</span><span class="material-symbols-outlined hover:text-primary cursor-pointer">share</span></div></div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const form = ref({
  company: '',
  email: '',
  phone: '',
  quantity: '',
  requirements: ''
})

const submitRequest = () => {
  alert('Yêu cầu báo giá đã được gửi (demo)')
  // Thực tế sẽ gửi request qua Inertia post
}
</script>

<style scoped>
/* Các style gốc đã được định nghĩa trong layout, không cần thêm trừ khi muốn ghi đè */
.glass-nav {
  backdrop-filter: blur(12px);
  background-color: rgba(251, 249, 245, 0.85);
}
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}
</style>