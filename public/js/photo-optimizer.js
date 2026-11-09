// Sequential decoding limits peak memory. Keep originals selected if preparation fails.
(function (root) {
    const MAX_SOURCE_BYTES = 32 * 1024 * 1024;
    const MAX_SOURCE_PIXELS = 48000000;
    const TARGET_BYTES = 1500 * 1024;
    const MAX_EDGE = 1600;
    const cache = new WeakMap();

    async function optimize(file) {
        if (file.size > MAX_SOURCE_BYTES) throw new Error(`${file.name}: photo is larger than 32 MB. Resize it before selecting.`);
        if (!/\.(jpe?g|png|webp|heic|heif)$/i.test(file.name) && !/^image\/(jpeg|png|webp|heic|heif)$/.test(file.type)) {
            throw new Error(`${file.name}: choose JPEG, PNG, WebP, HEIC or HEIF.`);
        }
        const url = URL.createObjectURL(file);
        const image = new Image();
        let canvas;
        try {
            await new Promise((resolve, reject) => {
                image.onload = resolve;
                image.onerror = () => reject(new Error(/heic|heif/i.test(file.name + file.type)
                    ? `${file.name}: this browser cannot convert HEIC/HEIF. Export as JPEG or use your camera's Most Compatible setting.`
                    : `${file.name}: the photo is corrupted or cannot be decoded.`));
                image.src = url;
            });
            if (image.naturalWidth < 100 || image.naturalHeight < 100) throw new Error(`${file.name}: photo must be at least 100 × 100 pixels.`);
            if (image.naturalWidth * image.naturalHeight > MAX_SOURCE_PIXELS) throw new Error(`${file.name}: photo exceeds 48 megapixels. Resize it first.`);
            const ratio = Math.min(1, MAX_EDGE / Math.max(image.naturalWidth, image.naturalHeight));
            canvas = document.createElement('canvas');
            canvas.width = Math.round(image.naturalWidth * ratio);
            canvas.height = Math.round(image.naturalHeight * ratio);
            const context = canvas.getContext('2d');
            if (!context) throw new Error('Your browser could not prepare the photo. Try a smaller JPEG.');
            context.fillStyle = '#fff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            let blob;
            for (const quality of [0.84, 0.72, 0.6]) {
                blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
                if (!blob) throw new Error(`${file.name}: photo conversion failed. Try a JPEG instead.`);
                if (blob.size <= TARGET_BYTES) break;
            }
            if (blob.size > TARGET_BYTES) throw new Error(`${file.name}: unable to optimize this photo. Resize it and retry.`);
            return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: file.lastModified });
        } finally {
            URL.revokeObjectURL(url);
            image.src = '';
            if (canvas) canvas.width = canvas.height = 0;
        }
    }

    async function prepare(files, progress = () => {}) {
        if (files.length > 5) throw new Error('Select a maximum of 5 photos.');
        const results = [];
        for (let i = 0; i < files.length; i++) {
            progress(`Optimizing photo ${i + 1} of ${files.length}…`);
            let result = cache.get(files[i]);
            if (!result) {
                result = await optimize(files[i]);
                cache.set(files[i], result);
            }
            results.push(result);
        }
        return results;
    }
    root.ReportPhotos = { optimize, prepare, MAX_SOURCE_BYTES, TARGET_BYTES };
})(typeof window === 'undefined' ? globalThis : window);
