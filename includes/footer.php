</main>
    <footer class="app-footer">
      <span>SleepWise &copy; 2026 &mdash; <a href="/cgu.php">CGU</a></span>
      <span>Développé par <strong>DeVolt</strong> &mdash; ISEP A1</span>
    </footer>
  </div>
</div>

<script src="/js/app.js"></script>
<?php if (!empty($extra_js)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script><?= $extra_js ?></script>
<?php endif; ?>
</body>
</html>