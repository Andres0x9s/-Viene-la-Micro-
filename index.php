<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <title>LuxeDrive</title>

  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    html {
      scroll-behavior: smooth;
    }

    body {
      background: #0b0b0b;
      overflow-x: hidden;
    }
  </style>
</head>

<body class="text-white font-sans">

  <!-- HEADER -->
  <header class="absolute top-0 left-0 w-full z-50 border-b border-white/10">
    <div class="max-w-[1400px] mx-auto px-8 py-6 flex items-center justify-between">

      <div class="text-3xl tracking-[0.45em] font-light">
        LUXEDRIVE
      </div>

      <nav class="hidden lg:flex items-center gap-10 uppercase text-[12px] tracking-[0.25em] text-gray-300">
        <a href="#" class="hover:text-white transition">Home</a>
        <a href="#" class="hover:text-white transition">Pages</a>
        <a href="#" class="hover:text-white transition">Fleet</a>
        <a href="#" class="hover:text-white transition">Services</a>
        <a href="#" class="hover:text-white transition">Blog</a>
        <a href="#" class="hover:text-white transition">Contact</a>
      </nav>

      <button class="border border-[#c8a97e] text-[#c8a97e] px-7 py-3 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-[#c8a97e] hover:text-black transition duration-300">
        Reservation
      </button>
    </div>
  </header>

  <!-- HERO -->
  <section class="relative min-h-screen flex items-center justify-center">

    <img
      src="https://images.unsplash.com/photo-1494905998402-395d579af36f?q=80&w=2000&auto=format&fit=crop"
      class="absolute inset-0 w-full h-full object-cover"
    />

    <div class="absolute inset-0 bg-black/70"></div>

    <div class="relative z-10 text-center px-6 max-w-6xl pt-32">

      <p class="uppercase tracking-[0.6em] text-[#c8a97e] text-sm mb-8">
        Premium Chauffeur Services
      </p>

      <h1 class="text-6xl md:text-8xl xl:text-[130px] leading-none font-light tracking-tight">
        Luxury
        <br />
        Redefined
      </h1>

      <div class="mt-14 flex justify-center gap-5 flex-wrap">

        <button class="bg-[#c8a97e] text-black px-10 py-4 rounded-full uppercase tracking-[0.2em] text-xs font-semibold hover:scale-105 transition duration-300">
          Explore Fleet
        </button>

        <button class="border border-white/20 backdrop-blur px-10 py-4 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-white hover:text-black transition duration-300">
          Our Services
        </button>

      </div>
    </div>

    <!-- BOOKING -->
    <div class="absolute bottom-[-80px] left-1/2 -translate-x-1/2 z-20 w-[92%] max-w-7xl">

      <div class="bg-[#111111] border border-white/10 rounded-[35px] p-8 shadow-2xl">

        <div class="grid lg:grid-cols-5 gap-5">

          <div>
            <label class="block text-xs uppercase tracking-[0.2em] text-gray-500 mb-3">
              Pick Up
            </label>

            <input
              class="w-full bg-[#1a1a1a] border border-white/10 rounded-2xl px-5 py-4 outline-none"
              placeholder="New York"
            />
          </div>

          <div>
            <label class="block text-xs uppercase tracking-[0.2em] text-gray-500 mb-3">
              Date
            </label>

            <input
              type="date"
              class="w-full bg-[#1a1a1a] border border-white/10 rounded-2xl px-5 py-4 outline-none"
            />
          </div>

          <div>
            <label class="block text-xs uppercase tracking-[0.2em] text-gray-500 mb-3">
              Return
            </label>

            <input
              type="date"
              class="w-full bg-[#1a1a1a] border border-white/10 rounded-2xl px-5 py-4 outline-none"
            />
          </div>

          <div>
            <label class="block text-xs uppercase tracking-[0.2em] text-gray-500 mb-3">
              Vehicle
            </label>

            <select class="w-full bg-[#1a1a1a] border border-white/10 rounded-2xl px-5 py-4 outline-none">
              <option>Business Class</option>
              <option>First Class</option>
              <option>SUV XL</option>
            </select>
          </div>

          <div class="flex items-end">
            <button class="w-full bg-[#c8a97e] text-black rounded-2xl py-4 uppercase tracking-[0.2em] text-xs font-bold hover:opacity-90 transition">
              Search Ride
            </button>
          </div>

        </div>
      </div>
    </div>
  </section>

  <!-- FLEET -->
  <section class="pt-44 pb-28 px-6 bg-[#0b0b0b]">

    <div class="max-w-[1400px] mx-auto">

      <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-10 mb-20">

        <div>

          <p class="uppercase tracking-[0.5em] text-[#c8a97e] text-xs mb-5">
            Our Fleet
          </p>

          <h2 class="text-5xl md:text-7xl font-light leading-tight">
            Choose The
            <br />
            Perfect Ride
          </h2>

        </div>

        <p class="text-gray-400 max-w-xl leading-relaxed text-lg">
          Discover elite transportation with world-class luxury vehicles,
          exceptional comfort and premium chauffeur experiences.
        </p>

      </div>

      <div class="grid lg:grid-cols-3 gap-8">

        <!-- CARD -->
        <div class="group relative overflow-hidden rounded-[35px] bg-[#111111] border border-white/10">

          <div class="overflow-hidden">
            <img
              src="https://images.unsplash.com/photo-1555215695-3004980ad54e?q=80&w=1400&auto=format&fit=crop"
              class="h-[520px] w-full object-cover group-hover:scale-105 transition duration-700"
            />
          </div>

          <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

          <div class="absolute bottom-0 left-0 p-10 w-full">

            <p class="uppercase tracking-[0.35em] text-[#c8a97e] text-xs mb-3">
              Business Class
            </p>

            <h3 class="text-4xl font-light mb-6">
              Mercedes-Benz E-Class
            </h3>

            <button class="border border-white/20 px-7 py-3 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-white hover:text-black transition duration-300">
              Book Vehicle
            </button>

          </div>
        </div>

        <!-- CARD -->
        <div class="group relative overflow-hidden rounded-[35px] bg-[#111111] border border-white/10">

          <div class="overflow-hidden">
            <img
              src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1400&auto=format&fit=crop"
              class="h-[520px] w-full object-cover group-hover:scale-105 transition duration-700"
            />
          </div>

          <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

          <div class="absolute bottom-0 left-0 p-10 w-full">

            <p class="uppercase tracking-[0.35em] text-[#c8a97e] text-xs mb-3">
              First Class
            </p>

            <h3 class="text-4xl font-light mb-6">
              Mercedes-Benz S-Class
            </h3>

            <button class="border border-white/20 px-7 py-3 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-white hover:text-black transition duration-300">
              Book Vehicle
            </button>

          </div>
        </div>

        <!-- CARD -->
        <div class="group relative overflow-hidden rounded-[35px] bg-[#111111] border border-white/10">

          <div class="overflow-hidden">
            <img
              src="https://images.unsplash.com/photo-1511919884226-fd3cad34687c?q=80&w=1400&auto=format&fit=crop"
              class="h-[520px] w-full object-cover group-hover:scale-105 transition duration-700"
            />
          </div>

          <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

          <div class="absolute bottom-0 left-0 p-10 w-full">

            <p class="uppercase tracking-[0.35em] text-[#c8a97e] text-xs mb-3">
              SUV XL
            </p>

            <h3 class="text-4xl font-light mb-6">
              Cadillac Escalade
            </h3>

            <button class="border border-white/20 px-7 py-3 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-white hover:text-black transition duration-300">
              Book Vehicle
            </button>

          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- EXPERIENCE -->
  <section class="bg-[#111111] py-32 px-6 border-y border-white/10">

    <div class="max-w-[1400px] mx-auto grid lg:grid-cols-2 gap-20 items-center">

      <div>

        <p class="uppercase tracking-[0.5em] text-[#c8a97e] text-xs mb-5">
          Why Choose Us
        </p>

        <h2 class="text-5xl md:text-7xl font-light leading-tight mb-10">
          Luxury In
          <br />
          Every Detail
        </h2>

        <div class="space-y-8 text-gray-400 text-lg leading-relaxed">

          <p>
            We deliver premium transportation experiences with impeccable
            service, elegant interiors and a world-class fleet.
          </p>

          <p>
            From airport transfers to executive events, our chauffeurs and
            vehicles redefine comfort and sophistication.
          </p>

        </div>

        <button class="mt-12 bg-[#c8a97e] text-black px-10 py-4 rounded-full uppercase tracking-[0.2em] text-xs font-semibold hover:scale-105 transition duration-300">
          Discover More
        </button>

      </div>

      <div class="relative">

        <img
          src="https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?q=80&w=1600&auto=format&fit=crop"
          class="rounded-[40px] h-[700px] w-full object-cover"
        />

        <div class="absolute -bottom-10 -left-10 bg-[#c8a97e] text-black p-10 rounded-[30px] w-64">

          <div class="text-6xl font-light mb-2">
            15+
          </div>

          <p class="uppercase tracking-[0.25em] text-xs">
            Years Of Experience
          </p>

        </div>

      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-black py-20 px-6">

    <div class="max-w-[1400px] mx-auto grid lg:grid-cols-4 gap-14 border-b border-white/10 pb-16">

      <div>

        <div class="text-3xl tracking-[0.45em] font-light mb-6">
          LUXEDRIVE
        </div>

        <p class="text-gray-500 leading-relaxed">
          Premium luxury transportation tailored for modern lifestyles.
        </p>

      </div>

      <div>

        <h4 class="uppercase tracking-[0.25em] text-sm mb-6 text-white">
          Navigation
        </h4>

        <ul class="space-y-4 text-gray-500">
          <li>Home</li>
          <li>Fleet</li>
          <li>Services</li>
          <li>Contact</li>
        </ul>

      </div>

      <div>

        <h4 class="uppercase tracking-[0.25em] text-sm mb-6 text-white">
          Services
        </h4>

        <ul class="space-y-4 text-gray-500">
          <li>Airport Transfers</li>
          <li>Business Travel</li>
          <li>VIP Chauffeur</li>
          <li>Wedding Cars</li>
        </ul>

      </div>

      <div>

        <h4 class="uppercase tracking-[0.25em] text-sm mb-6 text-white">
          Newsletter
        </h4>

        <div class="flex flex-col gap-4">

          <input
            placeholder="Your email"
            class="bg-[#111111] border border-white/10 rounded-full px-6 py-4 outline-none"
          />

          <button class="bg-[#c8a97e] text-black rounded-full py-4 uppercase tracking-[0.2em] text-xs font-bold">
            Subscribe
          </button>

        </div>

      </div>

    </div>

    <div class="text-center text-gray-600 text-sm pt-10 uppercase tracking-[0.2em]">
      LuxeDrive Inspired Design — 2026
    </div>

  </footer>

</body>
</html>