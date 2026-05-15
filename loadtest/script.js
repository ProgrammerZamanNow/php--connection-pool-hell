import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter, Rate } from 'k6/metrics';

http.setResponseCallback(http.expectedStatuses(201, 409));

export const options = {
    vus: 20,
    duration: '60s',
    thresholds: {
        http_req_failed: ['rate<0.05'],
        http_req_duration: ['p(95)<500'],
        server_errors: ['rate<0.01'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';

const PRODUCT_IDS = [1, 2, 3, 4];

const orderCreated = new Counter('orders_created');
const outOfStock = new Counter('orders_out_of_stock');
const serverErrors = new Rate('server_errors');

export default function () {
    const productId = PRODUCT_IDS[Math.floor(Math.random() * PRODUCT_IDS.length)];
    const quantity = Math.floor(Math.random() * 3) + 1;

    const payload = JSON.stringify({
        product_id: productId,
        quantity: quantity,
    });

    const params = {
        headers: { 'Content-Type': 'application/json' },
        tags: { name: 'POST /api/orders' },
    };

    const res = http.post(`${BASE_URL}/api/orders`, payload, params);

    const isCreated = res.status === 201;
    const isOutOfStock = res.status === 409;
    const isServerError = res.status >= 500;

    if (isCreated) orderCreated.add(1);
    if (isOutOfStock) outOfStock.add(1);
    serverErrors.add(isServerError);

    check(res, {
        'status is 201 or 409 (expected)': (r) => r.status === 201 || r.status === 409,
        'no server error (5xx)': (r) => r.status < 500,
        'response has status field': (r) => {
            try {
                return typeof r.json('status') === 'string';
            } catch (_e) {
                return false;
            }
        },
    });

    sleep(0.1);
}
